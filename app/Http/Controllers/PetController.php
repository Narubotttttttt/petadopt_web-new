<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pet;
use App\Models\AdoptionApplication;
use App\Models\TemperamentTag;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PetController extends Controller
{
    public function create()
    {
        $temperamentTags = TemperamentTag::orderBy('name')->get();

        return view('pets.create', [
            'pet' => new Pet(),
            'temperamentTags' => $temperamentTags,
        ]);
    }

    public function index()
    {
        $q = trim((string) request()->input('q', ''));

        $query = Pet::where('status', '!=', 'adopted');

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

            $query->where(function ($sub) use ($q, $extractedId) {
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

        $pets = $query->latest('created_at')->paginate(10)->withQueryString();

        return view('pets.index', [
            'pets' => $pets,
            'q' => $q,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'breed' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'type' => 'required|in:dog,cat',
            'age' => 'nullable|string|max:50',
            'medical_history' => 'nullable',
            'temperament_tags' => 'nullable|array',
            'temperament_tags.*' => 'exists:temperament_tags,id',
            'description' => 'nullable|string',
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

        $lockKey = 'pet_store_lock_' . ($request->session()->getId() ?: (Auth::id() ?? $request->ip()));
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 3);

        if (! $lock->get()) {
            return redirect()->route('pets.index')->with('success', 'Pet profile is already being submitted.');
        }

        try {
            $user = Auth::user();

            $pet = Pet::create([
                'breed' => $data['breed'],
                'color' => $data['color'],
                'gender' => $data['gender'],
                'type' => $data['type'],
                'age' => $data['age'] ?? null,
                'medical_history' => $data['medical_history'] ?? null,
                'description' => $data['description'] ?? null,
                'photo_path' => $data['photo_path'] ?? null,
                'status' => 'available',
                'added_by_user_id' => $user?->id,
                'added_by_name' => $user?->name,
            ]);

            $pet->temperamentTags()->sync($data['temperament_tags'] ?? []);

            session()->flash('success', 'Pet added successfully.');

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
        $data = $request->validate([
            'breed' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'type' => 'required|in:dog,cat',
            'age' => 'nullable|string|max:50',
            'medical_history' => 'nullable',
            'temperament_tags' => 'nullable|array',
            'temperament_tags.*' => 'exists:temperament_tags,id',
            'description' => 'nullable|string',
            'status' => ['required', 'in:available,pending,adopted'],
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

        $pet->update([
            'breed' => $data['breed'],
            'color' => $data['color'],
            'gender' => $data['gender'],
            'type' => $data['type'],
            'age' => $data['age'] ?? null,
            'medical_history' => $data['medical_history'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'photo_path' => $data['photo_path'] ?? $pet->photo_path,
        ]);

        $pet->temperamentTags()->sync($data['temperament_tags'] ?? []);

        session()->flash('success', 'Pet updated successfully.');

        return redirect()->route('pets.index');
    }

    public function destroy(Pet $pet)
    {
        if (Auth::user()?->role !== 'admin') {
            abort(403, 'Only an administrator can delete pet records.');
        }

        if ($pet->photo_path && Storage::disk('public')->exists($pet->photo_path)) {
            Storage::disk('public')->delete($pet->photo_path);
        }

        $pet->delete();

        session()->flash('success', 'Pet removed.');

        return redirect()->route('pets.index');
    }
}