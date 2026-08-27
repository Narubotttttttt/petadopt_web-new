<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdoptionApplication;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdoptionApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'pet_id'          => ['required', 'exists:pets,id'],
            'full_name'       => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:50'],
            'address'         => ['required', 'string'],
            'home_type'          => ['nullable', 'string'],
            'has_other_pets'     => ['nullable'],
            'other_pets_details' => ['nullable', 'string'],
            'has_experience'     => ['nullable'],
            'proposed_pet_name'  => ['nullable', 'string', 'max:255'],
            'reason'             => ['nullable', 'string'],
            'valid_id'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'barangay_certificate' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $user = $request->user();

        $messageParts = [];
        if ($request->filled('proposed_pet_name')) {
            $messageParts[] = "Proposed Pet Name: " . $request->proposed_pet_name;
        }
        if ($request->filled('address')) {
            $messageParts[] = "Address: " . $request->address;
        }
        if ($request->filled('home_type')) {
            $messageParts[] = "Home Type: " . $request->home_type;
        }
        if ($request->has('has_other_pets')) {
            $hasOtherPets = filter_var($request->has_other_pets, FILTER_VALIDATE_BOOLEAN);
            $messageParts[] = "Has Other Pets: " . ($hasOtherPets ? 'Yes' : 'No');
            $details = $request->input('other_pets_details');
            if ($hasOtherPets && !empty($details)) {
                $messageParts[] = "Other Pets Details: " . $details;
            }
        }
        if ($request->has('has_experience')) {
            $hasExp = filter_var($request->has_experience, FILTER_VALIDATE_BOOLEAN);
            $messageParts[] = "Has Pet Experience: " . ($hasExp ? 'Yes' : 'No');
        }
        if ($request->filled('reason')) {
            $messageParts[] = "Reason for Adoption:\n" . $request->reason;
        }

        $fullMessage = implode("\n", $messageParts);

        $validIdPath = null;
        if ($request->hasFile('valid_id')) {
            $validIdPath = $request->file('valid_id')->store('documents', 'public');
        }

        $barangayCertPath = null;
        if ($request->hasFile('barangay_certificate')) {
            $barangayCertPath = $request->file('barangay_certificate')->store('documents', 'public');
        }

        // Extract or record address to adopters_profile
        if (!empty($validated['address']) || !empty($user->email)) {
            $adopterAddress = $validated['address'] ?? null;
            if (empty($adopterAddress) && !empty($message) && preg_match('/Address:\s*(.+?)(?=\n[A-Za-z\s]+:|$)/is', $message, $m)) {
                $adopterAddress = trim($m[1]);
            }

            \App\Models\AdoptersProfile::updateOrCreate(
                ['email' => $user->email],
                [
                    'user_id'      => $user->id,
                    'adopter_code' => sprintf('ADP-%04d', $user->id),
                    'full_name'    => $user->name,
                    'phone'        => $validated['applicant_phone'] ?? null,
                    'address'      => $adopterAddress,
                    'city'         => 'Cagayan de Oro City',
                    'province'     => 'Misamis Oriental',
                    'status'       => 'active',
                ]
            );
        }

        $application = AdoptionApplication::create([
            'pet_id'                    => $request->pet_id,
            'applicant_name'            => $request->full_name,
            'applicant_email'           => $user->email ?? $request->email ?? 'adopter@email.com',
            'applicant_phone'           => $request->phone,
            'message'                   => $fullMessage,
            'valid_id_path'             => $validIdPath,
            'barangay_certificate_path' => $barangayCertPath,
            'status'                    => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Adoption application submitted successfully.',
            'data'    => [
                'id'         => $application->id,
                'status'     => $application->status,
                'created_at' => $application->created_at->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }

    public function myApplications(Request $request): JsonResponse
    {
        $user = $request->user();

        $applications = AdoptionApplication::with('pet')
            ->where('applicant_email', $user->email)
            ->latest()
            ->get()
            ->map(function ($app) {
                $pet = $app->pet;
                $photoUrl = null;
                if ($pet && $pet->photo_path) {
                    if (str_starts_with($pet->photo_path, 'http')) {
                        $photoUrl = $pet->photo_path;
                    } else {
                        $photoUrl = asset('storage/' . ltrim($pet->photo_path, '/'));
                    }
                }

                $isApproved = $app->status === 'approved';
                $isPetAdopted = $pet && $pet->status === 'adopted';
                $isAdoptedByOther = $isPetAdopted && ! $isApproved;

                $proposedName = null;
                if ($app->message && preg_match('/Proposed Pet Name:\s*(.+)/i', $app->message, $matches)) {
                    $proposedName = trim($matches[1]);
                }

                $displayName = ($pet && !empty($pet->name)) ? $pet->name : ($proposedName ?: ($pet ? ($pet->breed ?: 'Rescued Pet') : 'Pet'));

                return [
                    'id'               => $app->id,
                    'petName'          => $displayName,
                    'petBreed'         => $pet ? ($pet->breed ?: 'Mixed') : 'N/A',
                    'petType'          => $pet ? ucfirst($pet->type ?: 'Dog') : 'N/A',
                    'petImage'         => $photoUrl ?: 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=400&q=80',
                    'dateApplied'      => $app->created_at ? $app->created_at->format('M d, Y') : '',
                    'status'           => $app->status,
                    'isAdoptedByOther' => $isAdoptedByOther,
                    'scheduledAt'      => ($isApproved && $app->scheduled_at) ? $app->scheduled_at->format('l, M d, Y') : null,
                    'scheduledRaw'     => ($isApproved && $app->scheduled_at) ? $app->scheduled_at->format('Y-m-d H:i:s') : null,
                    'eventLocation'    => $isApproved ? $app->event_location : null,
                    'eventNotes'       => $isApproved ? $app->event_notes : null,
                    'updated_at'       => $app->updated_at ? $app->updated_at->toIso8601String() : null,
                    'created_at'       => $app->created_at ? $app->created_at->toIso8601String() : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $applications,
        ]);
    }

    public function vaccineReminders(Request $request): JsonResponse
    {
        $user = $request->user();

        $petIds = AdoptionApplication::where('applicant_email', $user->email)
            ->pluck('pet_id')
            ->unique()
            ->values();

        if ($petIds->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $today = now()->startOfDay();

        $reminders = \App\Models\MedicalLog::with('pet')
            ->whereIn('pet_id', $petIds)
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '>=', $today)
            ->orderBy('next_due_date')
            ->get()
            ->map(function ($log) use ($today) {
                $daysUntil = (int) $today->diffInDays($log->next_due_date, false);
                $pet = $log->pet;

                return [
                    'log_id'       => $log->id,
                    'pet_id'       => $log->pet_id,
                    'pet_name'     => ($pet && !empty($pet->name)) ? $pet->name : ('Pet no. ' . $log->pet_id),
                    'category'     => ucfirst(str_replace('_', ' ', $log->category ?? 'checkup')),
                    'next_due_date' => $log->next_due_date->format('Y-m-d'),
                    'next_due_label' => $log->next_due_date->format('M d, Y'),
                    'days_until_due' => $daysUntil,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $reminders,
        ]);
    }
    public function storeHealthUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'application_id' => ['required', 'exists:adoption_applications,id'],
            'photo'          => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'health_status'  => ['required', 'in:healthy,minor_issue,under_treatment'],
            'weight'         => ['nullable', 'numeric', 'min:0', 'max:200'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $application = AdoptionApplication::findOrFail($request->application_id);

        if ($application->applicant_email !== $user->email) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $photoPath = $request->file('photo')->store('health_updates', 'public');

        $healthUpdate = \App\Models\PetHealthUpdate::create([
            'adoption_application_id' => $application->id,
            'pet_id'                  => $application->pet_id,
            'user_id'                 => $user->id,
            'photo_path'              => $photoPath,
            'health_status'           => $request->health_status,
            'weight'                  => $request->weight,
            'notes'                   => $request->notes,
            'check_in_date'           => now()->toDateString(),
            'status'                  => 'submitted',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Health check-in submitted successfully.',
            'data'    => [
                'id'            => $healthUpdate->id,
                'photo_url'     => asset('storage/' . $photoPath),
                'health_status' => $healthUpdate->health_status,
                'weight'        => $healthUpdate->weight,
                'notes'         => $healthUpdate->notes,
                'check_in_date' => $healthUpdate->check_in_date->format('M d, Y'),
                'created_at'    => $healthUpdate->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function getHealthUpdates(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = \App\Models\PetHealthUpdate::where('user_id', $user->id);

        if ($request->filled('application_id')) {
            $query->where('adoption_application_id', $request->application_id);
        }

        $updates = $query->latest('check_in_date')->get()->map(function ($item) {
            return [
                'id'                      => $item->id,
                'adoption_application_id' => $item->adoption_application_id,
                'pet_id'                  => $item->pet_id,
                'photo_url'               => asset('storage/' . $item->photo_path),
                'health_status'           => $item->health_status,
                'weight'                  => $item->weight,
                'notes'                   => $item->notes,
                'check_in_date'           => $item->check_in_date ? $item->check_in_date->format('M d, Y') : '',
                'check_in_date_raw'       => $item->check_in_date ? $item->check_in_date->format('Y-m-d') : '',
                'status'                  => $item->status,
                'staff_remarks'           => $item->staff_remarks,
                'created_at'              => $item->created_at ? $item->created_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $updates,
        ]);
    }
}
