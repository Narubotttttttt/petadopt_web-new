<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PetApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Pet::with(['temperamentTags', 'medicalLogs'])->where('status', 'available');

        if ($request->filled('type') && strtolower($request->type) !== 'all') {
            $query->where('type', strtolower($request->type));
        }

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $extractedId = null;
            if (preg_match('/^(?:pet\s*(?:no\.?|#)?\s*|(?:no\.?|#)\s*)(\d+)$/i', $search, $matches)) {
                $extractedId = (int) $matches[1];
            } elseif (ctype_digit($search)) {
                $extractedId = (int) $search;
            } elseif (preg_match('/(?:pet\s*(?:no\.?|#)?\s*|(?:no\.?|#)\s*)(\d+)/i', $search, $matches)) {
                $extractedId = (int) $matches[1];
            }

            $query->where(function ($q) use ($search, $extractedId) {
                if ($extractedId !== null) {
                    $q->where('id', $extractedId);
                }

                $q->orWhereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(breed) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(color) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(type) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('temperamentTags', function ($tq) use ($search) {
                      $tq->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  });

                if ($extractedId === null) {
                    $q->orWhereRaw("LOWER(CONCAT('pet no. ', id)) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(CONCAT('pet no.', id)) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(CONCAT('pet #', id)) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(CONCAT('pet ', id)) LIKE ?", ["%{$search}%"]);
                }
            });
        }

        $user = $request->user('sanctum');
        $userEmail = $user?->email;
        $userApplications = !empty($userEmail)
            ? \App\Models\AdoptionApplication::where('applicant_email', $userEmail)
                ->whereIn('status', ['pending', 'under_review', 'approved'])
                ->get()
                ->keyBy('pet_id')
            : collect();

        $pets = $query->latest()->get()->map(function ($pet) use ($userApplications) {
            return $this->transformPet($pet, true, $userApplications->get($pet->id));
        });

        return response()->json([
            'success' => true,
            'data'    => $pets,
        ]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $pet = Pet::with(['temperamentTags', 'medicalLogs'])->find($id);

        if (! $pet) {
            return response()->json([
                'success' => false,
                'message' => 'Pet not found.',
            ], 404);
        }

        $user = $request->user('sanctum');
        $userEmail = $user?->email;
        $userApp = !empty($userEmail)
            ? \App\Models\AdoptionApplication::where('applicant_email', $userEmail)
                ->where('pet_id', $pet->id)
                ->whereIn('status', ['pending', 'under_review', 'approved'])
                ->first()
            : null;

        return response()->json([
            'success' => true,
            'data'    => $this->transformPet($pet, true, $userApp),
        ]);
    }

    private function transformPet(Pet $pet, bool $includeDetails = false, $userApp = null): array
    {
        $rootUrl = request() ? request()->getSchemeAndHttpHost() : config('app.url', 'http://localhost:8000');
        $photoUrl = null;
        if ($pet->photo_path) {
            if (str_starts_with($pet->photo_path, 'http')) {
                $photoUrl = $pet->photo_path;
            } else {
                $photoUrl = $rootUrl . '/storage/' . ltrim($pet->photo_path, '/');
            }
        }

        $data = [
            'id'                 => $pet->id,
            'name'               => $pet->name ?: ('Pet no. ' . $pet->id),
            'type'               => $pet->type ? ucfirst($pet->type) : null,
            'breed'              => $pet->breed ?: null,
            'color'              => $pet->color ?: null,
            'gender'             => $pet->gender ? ucfirst($pet->gender) : null,
            'age'                => $pet->age ?: null,
            'description'        => $pet->description ?: null,
            'image'              => $photoUrl,
            'photo_url'          => $photoUrl,
            'status'             => $pet->status,
            'temperament'        => $pet->temperamentTags->pluck('name')->toArray(),
            'has_applied'        => $userApp !== null,
            'application_status' => $userApp?->status,
            'application_id'     => $userApp?->id,
        ];

        if ($includeDetails) {
            $data['medical_history'] = $pet->medical_history ?: null;
            $data['medical_logs'] = $pet->medicalLogs->map(function ($log) {
                return [
                    'id'              => $log->id,
                    'category'        => $log->category,
                    'vaccine_name'    => $log->vaccine_name,
                    'title'           => $log->vaccine_name ?: ($log->category ? ucfirst(str_replace('_', ' ', $log->category)) : 'Clinical Entry'),
                    'date'            => $log->date ? $log->date->format('Y-m-d') : null,
                    'administered_by' => $log->administered_by,
                    'next_due_date'   => $log->next_due_date ? $log->next_due_date->format('Y-m-d') : null,
                    'notes'           => $log->administered_by ? ('Administered by ' . $log->administered_by) : null,
                ];
            });
        }

        return $data;
    }
}
