<?php

namespace App\Http\Controllers;

use App\Models\AdoptionApplication;
use App\Models\MedicalLog;
use App\Models\Pet;
use App\Models\TemperamentTag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PetController extends Controller
{
    protected function ensureDefaultTemperamentTags(): void
    {
        if (TemperamentTag::count() === 0) {
            $defaultTags = [
                'Affectionate',
                'Calm',
                'Energetic',
                'Friendly',
                'Independent',
                'Playful',
                'Protective',
                'Shy',
            ];
            foreach ($defaultTags as $name) {
                TemperamentTag::firstOrCreate(['name' => $name]);
            }
        }
    }

    public function create(Request $request)
    {
        $this->ensureDefaultTemperamentTags();
        $temperamentTags = TemperamentTag::orderBy('name')->get();

        $pet = new Pet();
        $status = $request->query('status');
        if (in_array($status, ['in_shelter', 'available'])) {
            $pet->status = $status;
        }

        return view('pets.create', [
            'pet' => $pet,
            'temperamentTags' => $temperamentTags,
        ]);
    }

    public function index()
    {
        $q = trim((string) request()->input('q', ''));
        $activeTab = request()->input('status', 'all');

        $baseQuery = Pet::query();

        // If not staff/admin (e.g. adopter browsing), only show available pets
        if (!in_array(Auth::user()?->role, ['admin', 'staff'])) {
            $baseQuery->where('status', 'available');
        }

        if ($q !== '') {
            // Extract possible pet ID from expressions like:
            // "pet no. 1", "pet no.1", "pet no 1", "pet #1", "pet 1", "no. 1", "no.1", "#1", "no 1", or pure digits like "1"
            $extractedId = null;
            if (preg_match('/^(?:pet\s*(?:no\.?|#)?\s*|(?:no\.?|#)\s*)(\d+)$/i', $q, $matches)) {
                $extractedId = (int) $matches[1];
            } elseif (ctype_digit($q)) {
                $extractedId = (int) $q;
            } elseif (preg_match('/(?:pet\s*(?:no\.?|#)?\s*|(?:no\.?|#)\s*)(\d+)/i', $q, $matches)) {
                $extractedId = (int) $matches[1];
            }

            $baseQuery->where(function ($sub) use ($q, $extractedId) {
                if ($extractedId !== null) {
                    $sub->where('id', $extractedId);
                }

                $sub->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('breed', 'like', "%{$q}%")
                    ->orWhere('color', 'like', "%{$q}%")
                    ->orWhere('type', 'like', "%{$q}%")
                    ->orWhere('gender', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('temperamentTags', function ($tq) use ($q) {
                        $tq->where('name', 'like', "%{$q}%");
                    });

                if ($extractedId === null) {
                    $sub->orWhereRaw("CONCAT('pet no. ', id) LIKE ?", ["%{$q}%"])
                        ->orWhereRaw("CONCAT('pet no.', id) LIKE ?", ["%{$q}%"])
                        ->orWhereRaw("CONCAT('pet #', id) LIKE ?", ["%{$q}%"])
                        ->orWhereRaw("CONCAT('pet ', id) LIKE ?", ["%{$q}%"])
                        ->orWhereRaw("CONCAT('#', id) LIKE ?", ["%{$q}%"]);
                }
            });
        }

        // Live counts for status tabs (respecting search query)
        $countQuery = clone $baseQuery;
        $counts = [
            'all'        => (clone $countQuery)->where('status', '!=', 'deceased')->count(),
            'in_shelter' => (clone $countQuery)->where('status', 'in_shelter')->count(),
            'available'  => (clone $countQuery)->where('status', 'available')->count(),
            'pending'    => (clone $countQuery)->where('status', 'pending')->count(),
            'adopted'    => (clone $countQuery)->where('status', 'adopted')->count(),
        ];

        // Apply status filter based on active tab
        $query = clone $baseQuery;
        switch ($activeTab) {
            case 'in_shelter':
                $query->where('status', 'in_shelter');
                break;
            case 'available':
                $query->where('status', 'available');
                break;
            case 'pending':
                $query->where('status', 'pending');
                break;
            case 'adopted':
                $query->where('status', 'adopted');
                break;
            case 'all':
            default:
                $activeTab = 'all';
                $query->where('status', '!=', 'deceased');
                break;
        }

        $pets = $query->latest('created_at')->paginate(10)->withQueryString();

        return view('pets.index', [
            'pets'      => $pets,
            'q'         => $q,
            'activeTab' => $activeTab,
            'counts'    => $counts,
        ]);
    }

    public function store(Request $request)
    {
        if (empty($request->breed)) {
            $request->merge([
                'breed' => $request->type === 'cat' ? 'Puspin (Mixed Breed)' : 'Aspin (Mixed Breed)'
            ]);
        }

        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'breed' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'size' => 'nullable|in:small,medium,large',
            'type' => 'required|in:dog,cat',
            'age' => 'nullable|string|max:50',
            'medical_history' => 'nullable',
            'temperament' => 'nullable|string|max:255',
            'custom_temperament' => 'nullable|string|max:255',
            'temperament_tags' => 'nullable|array',
            'temperament_tags.*' => 'exists:temperament_tags,id',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:in_shelter,available,pending,adopted,deceased',
            'rescue_date' => 'nullable|date',
            'rescue_location' => 'nullable|string|max:255',
            'intake_condition' => 'nullable|string|max:1000',
            'photo' => 'required|image|max:2048',
        ]);

        $medicalHistory = $request->input('medical_history');
        if (is_array($medicalHistory)) {
            $medicalHistory = implode(', ', array_values(array_filter($medicalHistory, fn ($value) => is_string($value) && trim($value) !== '')));
        } elseif (is_string($medicalHistory)) {
            $medicalHistory = trim($medicalHistory);
        }

        $data['medical_history'] = $medicalHistory ?? null;

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('pets', 'public');
        }

        $sessionId = $request->hasSession() ? $request->session()->getId() : null;
        $lockKey = 'pet_store_lock_' . ($sessionId ?: (Auth::id() ?? $request->ip()));
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 3);

        if (! $lock->get()) {
            return redirect()->route('pets.index')->with('success', 'Pet profile is already being submitted.');
        }

        try {
            $user = Auth::user();

            $pet = DB::transaction(function () use ($data, $user, $request) {
                $status = $data['status'] ?? 'in_shelter';
                $pet = Pet::create([
                    'name' => $data['name'] ?? null,
                    'breed' => $data['breed'],
                    'color' => $data['color'],
                    'gender' => $data['gender'],
                    'size' => $data['size'] ?? 'medium',
                    'type' => $data['type'],
                    'age' => $data['age'] ?? null,
                    'medical_history' => $data['medical_history'] ?? null,
                    'description' => $data['description'] ?? null,
                    'photo_path' => $data['photo_path'] ?? null,
                    'status' => $status,
                    'rescue_date' => $data['rescue_date'] ?? ($status === 'in_shelter' ? now()->toDateString() : null),
                    'rescue_location' => $data['rescue_location'] ?? null,
                    'intake_condition' => $data['intake_condition'] ?? null,
                    'added_by_user_id' => $user?->id,
                    'added_by_name' => $user?->name,
                ]);

                $tagIds = array_map('intval', $data['temperament_tags'] ?? []);
                $rawCustom = $request->input('custom_temperament') ?: $request->input('temperament');
                if (!empty($rawCustom)) {
                    $names = is_array($rawCustom)
                        ? $rawCustom
                        : array_map('trim', preg_split('/[,&]| and /i', (string) $rawCustom));

                    foreach ($names as $name) {
                        $name = trim($name);
                        if ($name !== '') {
                            $created = TemperamentTag::firstOrCreate(['name' => ucfirst($name)]);
                            $tagIds[] = $created->id;
                        }
                    }
                }

                $pet->temperamentTags()->sync(array_values(array_unique($tagIds)));

                // 1. Auto-create clinical MedicalLog for Vaccination if checked
                if ($request->boolean('is_vaccinated')) {
                    $vaccineName = trim((string) $request->input('vaccine_name')) ?: '5-in-1';
                    $vaccineDate = $request->input('vaccine_date') ?: now()->toDateString();
                    $vaccineNextDue = $request->input('vaccine_next_due') ?: Carbon::parse($vaccineDate)->addMonths(6)->toDateString();

                    MedicalLog::create([
                        'pet_id'          => $pet->id,
                        'category'        => 'vaccination',
                        'vaccine_name'    => $vaccineName,
                        'date'            => $vaccineDate,
                        'next_due_date'   => $vaccineNextDue,
                        'administered_by' => $user?->name ?? 'Shelter Clinician',
                        'created_by'      => $user?->id,
                    ]);
                }

                // 2. Auto-create clinical MedicalLog for Deworming if checked
                if ($request->boolean('is_dewormed')) {
                    $dewormerName = trim((string) $request->input('dewormer_name')) ?: 'Heartgard Plus';
                    $dewormerDate = $request->input('dewormer_date') ?: now()->toDateString();
                    $dewormerNextDue = $request->input('dewormer_next_due') ?: Carbon::parse($dewormerDate)->addMonths(3)->toDateString();

                    MedicalLog::create([
                        'pet_id'          => $pet->id,
                        'category'        => 'deworming',
                        'vaccine_name'    => $dewormerName,
                        'date'            => $dewormerDate,
                        'next_due_date'   => $dewormerNextDue,
                        'administered_by' => $user?->name ?? 'Shelter Clinician',
                        'created_by'      => $user?->id,
                    ]);
                }

                return $pet;
            });

            $petIdentifier = $pet->name ?: ('Pet no. ' . $pet->id);
            session()->flash('success', 'Pet "' . $petIdentifier . '" was added successfully.');
            session()->flash('new_pet_id', $pet->id);

            return redirect()->route('pets.index');
        } finally {
            $lock->release();
        }
    }

    public function show(Pet $pet)
    {
        $pet->load(['medicalLogs.creator', 'temperamentTags', 'addedBy.staffProfile']);

        return view('pets.show', [
            'pet' => $pet,
        ]);
    }

    public function edit(Pet $pet)
    {
        $this->ensureDefaultTemperamentTags();
        $temperamentTags = TemperamentTag::orderBy('name')->get();
        $selectedTagIds = $pet->temperamentTags()->pluck('temperament_tags.id')->toArray();

        return view('pets.edit', [
            'pet' => $pet,
            'temperamentTags' => $temperamentTags,
            'selectedTagIds' => $selectedTagIds,
        ]);
    }

    public function update(Request $request, Pet $pet)
    {
        if (empty($request->breed)) {
            $request->merge([
                'breed' => $request->type === 'cat' ? 'Puspin (Mixed Breed)' : ($pet->breed ?: 'Aspin (Mixed Breed)')
            ]);
        }

        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'breed' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'size' => 'nullable|in:small,medium,large',
            'type' => 'required|in:dog,cat',
            'age' => 'nullable|string|max:50',
            'medical_history' => 'nullable',
            'temperament' => 'nullable|string|max:255',
            'custom_temperament' => 'nullable|string|max:255',
            'temperament_tags' => 'nullable|array',
            'temperament_tags.*' => 'exists:temperament_tags,id',
            'description' => 'nullable|string',
            'status' => ['required', 'in:in_shelter,available,pending,adopted,deceased'],
            'rescue_date' => 'nullable|date',
            'rescue_location' => 'nullable|string|max:255',
            'intake_condition' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:2048',
        ]);

        $medicalHistory = $request->input('medical_history');
        if (is_array($medicalHistory)) {
            $medicalHistory = implode(', ', array_values(array_filter($medicalHistory, fn ($value) => is_string($value) && trim($value) !== '')));
        } elseif (is_string($medicalHistory)) {
            $medicalHistory = trim($medicalHistory);
        }

        $data['medical_history'] = $medicalHistory ?? null;

        if ($request->hasFile('photo')) {
            if ($pet->photo_path && Storage::disk('public')->exists($pet->photo_path)) {
                Storage::disk('public')->delete($pet->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('pets', 'public');
        }

        DB::transaction(function () use ($pet, $data, $request) {
            $pet->update([
                'name' => $data['name'] ?? $pet->name,
                'breed' => $data['breed'],
                'color' => $data['color'],
                'gender' => $data['gender'],
                'size' => $data['size'] ?? $pet->size ?? 'medium',
                'type' => $data['type'],
                'age' => $data['age'] ?? null,
                'medical_history' => $data['medical_history'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
                'rescue_date' => $data['rescue_date'] ?? $pet->rescue_date,
                'rescue_location' => $data['rescue_location'] ?? $pet->rescue_location,
                'intake_condition' => $data['intake_condition'] ?? $pet->intake_condition,
                'photo_path' => $data['photo_path'] ?? $pet->photo_path,
            ]);

            $tagIds = array_map('intval', $data['temperament_tags'] ?? []);
            $rawCustom = $request->input('custom_temperament') ?: $request->input('temperament');
            if (!empty($rawCustom)) {
                $names = is_array($rawCustom)
                    ? $rawCustom
                    : array_map('trim', preg_split('/[,&]| and /i', (string) $rawCustom));

                foreach ($names as $name) {
                    $name = trim($name);
                    if ($name !== '') {
                        $created = TemperamentTag::firstOrCreate(['name' => ucfirst($name)]);
                        $tagIds[] = $created->id;
                    }
                }
            }

            $pet->temperamentTags()->sync(array_values(array_unique($tagIds)));

            // 1. Update or create clinical MedicalLog for Vaccination if checked
            if ($request->boolean('is_vaccinated')) {
                $vaccineName = trim((string) $request->input('vaccine_name')) ?: '5-in-1';
                $vaccineDate = $request->input('vaccine_date') ?: now()->toDateString();
                $vaccineNextDue = $request->input('vaccine_next_due') ?: Carbon::parse($vaccineDate)->addMonths(6)->toDateString();

                $vLog = MedicalLog::where('pet_id', $pet->id)->where('category', 'vaccination')->latest('date')->first();
                if ($vLog) {
                    $vLog->update([
                        'vaccine_name'  => $vaccineName,
                        'date'          => $vaccineDate,
                        'next_due_date' => $vaccineNextDue,
                    ]);
                } else {
                    MedicalLog::create([
                        'pet_id'          => $pet->id,
                        'category'        => 'vaccination',
                        'vaccine_name'    => $vaccineName,
                        'date'            => $vaccineDate,
                        'next_due_date'   => $vaccineNextDue,
                        'administered_by' => Auth::user()?->name ?? 'Shelter Clinician',
                        'created_by'      => Auth::id(),
                    ]);
                }
            }

            // 2. Update or create clinical MedicalLog for Deworming if checked
            if ($request->boolean('is_dewormed')) {
                $dewormerName = trim((string) $request->input('dewormer_name')) ?: 'Heartgard Plus';
                $dewormerDate = $request->input('dewormer_date') ?: now()->toDateString();
                $dewormerNextDue = $request->input('dewormer_next_due') ?: Carbon::parse($dewormerDate)->addMonths(3)->toDateString();

                $dLog = MedicalLog::where('pet_id', $pet->id)->where('category', 'deworming')->latest('date')->first();
                if ($dLog) {
                    $dLog->update([
                        'vaccine_name'  => $dewormerName,
                        'date'          => $dewormerDate,
                        'next_due_date' => $dewormerNextDue,
                    ]);
                } else {
                    MedicalLog::create([
                        'pet_id'          => $pet->id,
                        'category'        => 'deworming',
                        'vaccine_name'    => $dewormerName,
                        'date'            => $dewormerDate,
                        'next_due_date'   => $dewormerNextDue,
                        'administered_by' => Auth::user()?->name ?? 'Shelter Clinician',
                        'created_by'      => Auth::id(),
                    ]);
                }
            }
        });

        $petIdentifier = $pet->name ?: ('Pet no. ' . $pet->id);
        session()->flash('success', 'Pet "' . $petIdentifier . '" was updated successfully.');
        session()->flash('new_pet_id', $pet->id);

        return redirect()->route('pets.index');
    }

    public function markAvailable(Pet $pet)
    {
        if (!in_array(Auth::user()?->role, ['admin', 'staff'])) {
            abort(403, 'Unauthorized.');
        }

        $pet->update([
            'status' => 'available',
        ]);

        return redirect()->back()->with('success', ($pet->name ?: ('Pet no. ' . $pet->id)) . ' is now marked as Ready for Adoption.');
    }

    public function destroy(Pet $pet)
    {
        if (Auth::user()?->role !== 'admin') {
            abort(403, 'Only an administrator can delete pet records.');
        }

        if ($pet->photo_path && Storage::disk('public')->exists($pet->photo_path)) {
            Storage::disk('public')->delete($pet->photo_path);
        }

        $petIdentifier = $pet->name ?: ('Pet no. ' . $pet->id);
        $pet->delete();

        session()->flash('success', 'Pet "' . $petIdentifier . '" was removed.');

        return redirect()->route('pets.index');
    }
}