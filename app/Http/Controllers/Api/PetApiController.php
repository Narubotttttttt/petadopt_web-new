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
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(type) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('temperamentTags', function ($tq) use ($search) {
                      $tq->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  });
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
            'type'               => ucfirst($pet->type ?: 'Dog'),
            'breed'              => $pet->breed ?: 'Mixed Breed',
            'color'              => $pet->color ?: 'N/A',
            'gender'             => ucfirst($pet->gender ?: 'Unknown'),
            'age'                => $pet->age ?: 'Unknown',
            'description'        => $pet->description ?: 'No description provided.',
            'image'              => $photoUrl ?: 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=400&q=80',
            'status'             => $pet->status,
            'temperament'        => $pet->temperamentTags->pluck('name')->toArray(),
            'has_applied'        => $userApp !== null,
            'application_status' => $userApp?->status,
            'application_id'     => $userApp?->id,
        ];

        if ($includeDetails) {
            $data['medical_history'] = $pet->medical_history ?: 'No medical history recorded.';
            $data['medical_logs'] = $pet->medicalLogs->map(function ($log) {
                return [
                    'id'          => $log->id,
                    'title'       => $log->title ?? 'Checkup',
                    'date'        => $log->date ? $log->date->format('Y-m-d') : null,
                    'notes'       => $log->notes,
                ];
            });
        }

        return $data;
    }
}
