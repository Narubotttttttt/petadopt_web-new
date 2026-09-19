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
        $validated = $request->validate([
            'pet_id'          => ['required', 'exists:pets,id'],
            'full_name'       => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:50'],
            'address'         => ['required', 'string'],
            'id_type'         => ['nullable', 'string', 'max:100'],
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
        $applicantEmail = strtolower(trim($user?->email ?: $request->input('applicant_email', $request->input('email', ''))));

        $adopterProfile = null;
        if ($user) {
            $adopterProfile = \App\Models\AdoptersProfile::where('user_id', $user->id)
                ->orWhere('email', strtolower(trim($user->email)))
                ->first();
        }
        if (!$adopterProfile && !empty($applicantEmail)) {
            $adopterProfile = \App\Models\AdoptersProfile::where('email', $applicantEmail)->first();
        }

        if ($adopterProfile && in_array($adopterProfile->status, ['blacklisted', 'restricted'])) {
            $statusName = $adopterProfile->status === 'blacklisted' ? 'Banned / Blacklisted' : 'Restricted';
            $reason = !empty($adopterProfile->admin_notes) ? " Reason: {$adopterProfile->admin_notes}." : "";
            return response()->json([
                'success' => false,
                'message' => "You are prohibited from submitting adoption applications. Your account is {$statusName}.{$reason} Please contact the shelter office for assistance.",
            ], 403);
        }

        $existing = AdoptionApplication::where('pet_id', $request->pet_id)
            ->where(function ($q) use ($user, $request) {
                if ($user && !empty($user->email)) {
                    $q->where('applicant_email', $user->email);
                } elseif ($request->filled('email')) {
                    $q->where('applicant_email', $request->email);
                }
            })
            ->whereIn('status', ['pending', 'under_review', 'approved'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active adoption application for this pet (' . ucfirst(str_replace('_', ' ', $existing->status)) . ').',
                'application_id' => $existing->id,
                'status' => $existing->status,
            ], 422);
        }

        $messageParts = [];
        if ($request->filled('id_type')) {
            $messageParts[] = "Valid ID Type: " . $request->id_type;
        }
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
        $adopterAddress = $validated['address'] ?? null;
        $adopterPhone = $validated['phone'] ?? null;
        if (!empty($user?->email)) {
            $existingProf = \App\Models\AdoptersProfile::where('email', $user->email)->first();
            $profStatus = $existingProf?->status ?? 'active';

            \App\Models\AdoptersProfile::updateOrCreate(
                ['email' => $user->email],
                [
                    'user_id'      => $user->id,
                    'adopter_code' => sprintf('ADP-%04d', $user->id),
                    'full_name'    => $user->name,
                    'phone'        => $adopterPhone ?: ($existingProf?->phone ?: $user->phone),
                    'address'      => $adopterAddress ?: $existingProf?->address,
                    'city'         => $existingProf?->city ?: 'Cagayan de Oro City',
                    'province'     => $existingProf?->province ?: 'Misamis Oriental',
                    'status'       => $profStatus,
                ]
            );
        }

        $application = AdoptionApplication::create([
            'pet_id'                    => $request->pet_id,
            'applicant_name'            => $request->full_name,
            'applicant_email'           => $user->email ?? $request->email ?? 'adopter@email.com',
            'applicant_phone'           => $request->phone,
            'id_type'                   => $request->input('id_type', 'Philippine National ID (PhilSys)'),
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

        $rootUrl = $request->getSchemeAndHttpHost();

        $applications = AdoptionApplication::with('pet', 'staff')
            ->where('applicant_email', $user->email)
            ->latest()
            ->get()
            ->map(function ($app) use ($rootUrl) {
                $pet = $app->pet;
                $photoUrl = null;
                if ($pet && $pet->photo_path) {
                    if (str_starts_with($pet->photo_path, 'http')) {
                        $photoUrl = $pet->photo_path;
                    } else {
                        $photoUrl = $rootUrl . '/storage/' . ltrim($pet->photo_path, '/');
                    }
                }

                $isApproved = $app->status === 'approved';
                $isPetAdopted = $pet && $pet->status === 'adopted';
                $isAdoptedByOther = $isPetAdopted && ! $isApproved;

                $proposedName = null;
                if ($app->message && preg_match('/Proposed Pet Name:\s*([^\r\n]+)/i', $app->message, $matches)) {
                    $proposedName = trim($matches[1]);
                }

                // If adopter proposed a custom name, that is the adopted pet's name.
                // Otherwise use the pet's existing name if set and not empty.
                // If neither, default to "Adopted Pet" (never fall back to pet->breed).
                $adoptedPetName = $proposedName ?: (($pet && !empty($pet->name)) ? $pet->name : 'Adopted Pet');

                $shelterPetId = $pet ? $pet->id : (int)$app->pet_id;
                $shelterPetCode = 'Pet no. ' . $shelterPetId;

                $sigUrl = $app->signature_path ? (str_starts_with($app->signature_path, 'http') ? $app->signature_path : $rootUrl . '/storage/' . ltrim($app->signature_path, '/')) : null;
                $staffSigUrl = $app->staff_signature_url;

                return [
                    'id'               => $app->id,
                    'pet_id'           => (int)$app->pet_id,
                    'petId'            => (int)$app->pet_id,
                    'shelterPetId'     => $shelterPetId,
                    'shelterPetCode'   => $shelterPetCode,
                    'shelter_id'       => $shelterPetId,
                    'shelter_code'     => $shelterPetCode,
                    'pet'              => $pet ? [
                        'id'           => (int)$pet->id,
                        'name'         => $adoptedPetName,
                        'shelter_id'   => (int)$pet->id,
                        'shelter_code' => $shelterPetCode,
                        'type'         => $pet->type,
                        'breed'        => $pet->breed,
                        'photo_url'    => $photoUrl,
                    ] : null,
                    'petName'          => $adoptedPetName,
                    'adoptedPetName'   => $adoptedPetName,
                    'proposedName'     => $proposedName,
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
                    'rejection_reason' => $app->rejection_reason ?? $app->evaluation_notes,
                    'rejectionReason'  => $app->rejection_reason ?? $app->evaluation_notes,
                    'signature_url'    => $sigUrl,
                    'staff_signature_url' => $staffSigUrl,
                    'valid_id_url'     => $app->valid_id_url,
                    'barangay_certificate_url' => $app->barangay_certificate_url,
                    'signed_at'        => $app->signed_at ? $app->signed_at->format('M d, Y h:i A') : null,
                    'is_signed'        => !empty($app->signature_path),
                    'signature_required' => $isApproved && empty($app->signature_path),
                    'updated_at'       => $app->updated_at ? $app->updated_at->toIso8601String() : null,
                    'created_at'       => $app->created_at ? $app->created_at->toIso8601String() : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $applications,
        ]);
    }

    public function signContract(Request $request, $id): JsonResponse
    {
        $request->validate([
            'signature_data'      => ['nullable', 'string'],
            'use_saved_signature' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $application = AdoptionApplication::with('pet')->findOrFail($id);

        if ($application->applicant_email !== $user->email) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        if (!in_array($application->status, ['approved', 'adopted'])) {
            return response()->json([
                'success' => false,
                'message' => 'Signing is only allowed for approved or adopted applications.',
            ], 400);
        }

        // Case A: Using on-record saved signature (Option 1)
        if ($request->boolean('use_saved_signature') || empty($request->input('signature_data'))) {
            $savedPath = $user->digital_signature_path;
            if (empty($savedPath)) {
                $profile = \App\Models\AdoptersProfile::where('email', $user->email)->first();
                $savedPath = $profile ? $profile->digital_signature_path : null;
            }

            if (!empty($savedPath) && \Illuminate\Support\Facades\Storage::disk('public')->exists($savedPath)) {
                $application->update([
                    'signature_path' => $savedPath,
                    'signed_at'      => now(),
                ]);

                return response()->json([
                    'success'       => true,
                    'message'       => 'Adoption contract signed successfully using your on-record signature.',
                    'signature_url' => $application->signature_url,
                    'signed_at'     => $application->signed_at->format('M d, Y h:i A'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No saved digital signature on record. Please draw your signature.',
            ], 422);
        }

        // Case B: Newly drawn signature
        $sigData = $request->signature_data;
        if (preg_match('/^data:image\/(\w+);base64,/', $sigData, $type)) {
            $sigData = substr($sigData, strpos($sigData, ',') + 1);
        }
        $decoded = base64_decode($sigData);
        if (!$decoded) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature image data.',
            ], 422);
        }

        $fileName = 'signatures/sig_app_' . $application->id . '_' . time() . '.png';
        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $decoded);

        // 1. Update Adoption Application
        $application->update([
            'signature_path' => $fileName,
            'signed_at'      => now(),
        ]);

        // 2. Auto-sync to AdoptersProfile
        \App\Models\AdoptersProfile::updateOrCreate(
            ['email' => $user->email],
            [
                'user_id'                => $user->id,
                'adopter_code'           => sprintf('ADP-%04d', $user->id),
                'full_name'              => $user->name,
                'digital_signature_path' => $fileName,
                'status'                 => 'active',
            ]
        );

        return response()->json([
            'success'       => true,
            'message'       => 'Adoption contract signed and saved to your profile successfully!',
            'signature_url' => asset('storage/' . $fileName),
            'signed_at'     => $application->signed_at->format('M d, Y h:i A'),
        ]);
    }

    public function updateUserSignature(Request $request): JsonResponse
    {
        $request->validate([
            'signature_data' => ['required', 'string'],
        ]);

        $user = $request->user();
        $sigData = $request->signature_data;
        if (preg_match('/^data:image\/(\w+);base64,/', $sigData, $type)) {
            $sigData = substr($sigData, strpos($sigData, ',') + 1);
        }
        $decoded = base64_decode($sigData);
        if (!$decoded) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature image data.',
            ], 422);
        }

        $fileName = 'signatures/sig_user_' . $user->id . '_' . time() . '.png';
        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $decoded);

        \App\Models\AdoptersProfile::updateOrCreate(
            ['email' => $user->email],
            [
                'user_id'                => $user->id,
                'adopter_code'           => sprintf('ADP-%04d', $user->id),
                'full_name'              => $user->name,
                'digital_signature_path' => $fileName,
                'status'                 => 'active',
            ]
        );

        return response()->json([
            'success'               => true,
            'message'               => 'Digital signature updated successfully.',
            'digital_signature_url' => asset('storage/' . $fileName),
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

    public function petMedicalPassport(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $pet = Pet::with(['medicalLogs.creator'])->findOrFail($id);

        $isStaff = in_array($user->role, ['admin', 'staff']);
        $application = AdoptionApplication::where('pet_id', $pet->id)
            ->where('applicant_email', $user->email)
            ->latest()
            ->first();

        if (!$isStaff && !$application) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have an adoption record for this pet.',
            ], 403);
        }

        if (!$application) {
            $application = AdoptionApplication::where('pet_id', $pet->id)
                ->whereIn('status', ['approved', 'adopted'])
                ->latest()
                ->first();
        }

        $adopterProfile = null;
        if ($application) {
            $adopterProfile = \App\Models\AdoptersProfile::where('email', $application->applicant_email)->first();
        }
        if (!$adopterProfile && $user->role === 'adopter') {
            $adopterProfile = \App\Models\AdoptersProfile::where('email', $user->email)->first();
        }

        $today = now()->startOfDay();
        $thirtyDaysAhead = $today->copy()->addDays(30)->endOfDay();
        $logs = $pet->medicalLogs->sortByDesc('date');
        $latestVaccine = $logs->where('category', 'vaccination')->first();
        $latestDeworming = $logs->where('category', 'deworming')->first();

        $vacDue = $latestVaccine?->next_due_date ? $latestVaccine->next_due_date->copy()->startOfDay() : null;
        $isVacOverdue = $vacDue && $vacDue->lt($today);
        $isVacDueSoon = $vacDue && !$isVacOverdue && $vacDue->lte($thirtyDaysAhead);
        $daysUntilBooster = $vacDue ? (int) $today->diffInDays($vacDue, false) : null;

        $vacStatus = 'Not Vaccinated';
        if ($latestVaccine) {
            if ($isVacOverdue) {
                $vacStatus = 'Booster Overdue';
            } elseif ($isVacDueSoon) {
                $vacStatus = 'Booster Due Soon';
            } else {
                $vacStatus = 'Up to Date';
            }
        }

        $dewormDue = $latestDeworming?->next_due_date ? $latestDeworming->next_due_date->copy()->startOfDay() : null;
        $isDewormOverdue = $dewormDue && $dewormDue->lt($today);
        $isDewormDueSoon = $dewormDue && !$isDewormOverdue && $dewormDue->lte($thirtyDaysAhead);
        $daysUntilDeworm = $dewormDue ? (int) $today->diffInDays($dewormDue, false) : null;

        $dewormStatus = 'Not Dewormed';
        if ($latestDeworming) {
            if ($isDewormOverdue) {
                $dewormStatus = 'Dose Overdue';
            } elseif ($isDewormDueSoon) {
                $dewormStatus = 'Dose Due Soon';
            } else {
                $dewormStatus = 'Up to Date';
            }
        }

        $formattedLogs = $logs->values()->map(function ($log) use ($today, $thirtyDaysAhead) {
            $nextDue = $log->next_due_date ? $log->next_due_date->copy()->startOfDay() : null;
            $isOverdue = $nextDue && $nextDue->lt($today);
            $isDueSoon = $nextDue && !$isOverdue && $nextDue->lte($thirtyDaysAhead);

            return [
                'id'                 => $log->id,
                'category'           => $log->category,
                'category_label'     => ucfirst(str_replace('_', ' ', $log->category)),
                'vaccine_name'       => $log->vaccine_name,
                'date'               => $log->date?->format('Y-m-d'),
                'date_formatted'     => $log->date?->format('M d, Y'),
                'next_due_date'      => $log->next_due_date?->format('Y-m-d'),
                'next_due_formatted' => $log->next_due_date?->format('M d, Y'),
                'administered_by'    => $log->administered_by ?: ($log->creator?->name ?? 'CAWS Veterinary Officer'),
                'is_overdue'         => $isOverdue,
                'is_due_soon'        => $isDueSoon,
            ];
        });

        $photoUrl = $pet->photo_path
            ? (str_starts_with($pet->photo_path, 'http') ? $pet->photo_path : asset('storage/' . ltrim($pet->photo_path, '/')))
            : null;

        $cardData = [
            'shelter_info' => [
                'organization'   => 'CDO Animal Welfare Society (CAWS)',
                'address'        => 'Cagayan de Oro City, Philippines',
                'contact_email'  => 'caws.cdo@gmail.com',
                'passport_title' => 'OFFICIAL PET CARD',
                'card_title'     => 'OFFICIAL PET CARD',
                'issued_date'    => now()->format('M d, Y'),
            ],
            'pet' => [
                'id'              => $pet->id,
                'registry_number' => 'CAWS-PET-' . str_pad($pet->id, 5, '0', STR_PAD_LEFT),
                'name'            => !empty($pet->name) ? $pet->name : ('Pet no. ' . $pet->id),
                'type'            => ucfirst($pet->type ?? 'Pet'),
                'breed'           => $pet->breed ?? 'Mixed Breed',
                'age'             => $pet->age ?? 'N/A',
                'gender'          => ucfirst($pet->gender ?? 'Unknown'),
                'color'           => $pet->color ?? 'Standard',
                'photo_url'       => $photoUrl,
                'status'          => $pet->status,
                'medical_history' => $pet->medical_history,
            ],
            'guardian' => [
                'name'               => $application?->applicant_name ?? $adopterProfile?->full_name ?? $user->name,
                'adopter_code'       => $adopterProfile?->adopter_code ?? sprintf('ADP-%04d', $user->id),
                'phone'              => $application?->applicant_phone ?? $adopterProfile?->phone ?? $user->phone ?? 'N/A',
                'email'              => $application?->applicant_email ?? $adopterProfile?->email ?? $user->email,
                'address'            => $adopterProfile?->address ?? 'Cagayan de Oro City',
                'application_id'     => $application?->id,
                'application_status' => $application?->status,
            ],
            'clinical_summary' => [
                'vaccine_status'          => $vacStatus,
                'is_vaccine_overdue'      => $isVacOverdue,
                'is_vaccine_due_soon'     => $isVacDueSoon,
                'days_until_booster'      => $daysUntilBooster,
                'latest_vaccine_date'     => $latestVaccine?->date?->format('M d, Y'),
                'latest_vaccine_name'     => $latestVaccine?->vaccine_name,
                'next_vaccine_due_date'   => $latestVaccine?->next_due_date?->format('M d, Y'),

                'deworming_status'        => $dewormStatus,
                'is_deworming_overdue'    => $isDewormOverdue,
                'is_deworming_due_soon'   => $isDewormDueSoon,
                'days_until_deworming'    => $daysUntilDeworm,
                'latest_deworming_date'   => $latestDeworming?->date?->format('M d, Y'),
                'latest_deworming_name'   => $latestDeworming?->vaccine_name,
                'next_deworming_due_date' => $latestDeworming?->next_due_date?->format('M d, Y'),

                'total_records_count'     => $logs->count(),
            ],
            'records' => $formattedLogs,
        ];

        return response()->json([
            'success'  => true,
            'passport' => $cardData,
            'pet_card' => $cardData,
        ]);
    }

    public function updatePetName(Request $request, int|string $id): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'min:1', 'max:100'],
        ]);

        $user = $request->user();
        $pet = Pet::findOrFail($id);

        // Verify the user has an active application or adoption for this pet
        $application = AdoptionApplication::where('applicant_email', $user->email)
            ->where('pet_id', $pet->id)
            ->whereIn('status', ['approved', 'adopted', 'pending', 'under_review'])
            ->latest()
            ->first();

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have an active application or adoption for this pet.',
            ], 403);
        }

        $newName = trim($request->name);

        // 1. Update the Pet's official name in the pets table so all admin/staff web records display it immediately
        $pet->update(['name' => $newName]);

        // 2. Also update or append "Proposed Pet Name: ..." in the application message
        if ($application->message && preg_match('/Proposed Pet Name:\s*([^\r\n]+)/i', $application->message)) {
            $updatedMessage = preg_replace('/Proposed Pet Name:\s*([^\r\n]+)/i', 'Proposed Pet Name: ' . $newName, $application->message);
        } else {
            $updatedMessage = "Proposed Pet Name: " . $newName . ($application->message ? ("\n" . $application->message) : "");
        }
        $application->update(['message' => $updatedMessage]);

        return response()->json([
            'success'  => true,
            'message'  => 'Pet name updated successfully.',
            'pet_name' => $newName,
            'pet'      => [
                'id'           => $pet->id,
                'name'         => $pet->name,
                'shelter_id'   => $pet->id,
                'shelter_code' => 'Pet no. ' . $pet->id,
            ],
        ]);
    }
}

