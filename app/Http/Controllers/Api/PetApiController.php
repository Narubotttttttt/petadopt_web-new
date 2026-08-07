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

        $pets = $query->latest()->get()->map(function ($pet) {
            return $this->transformPet($pet, true);
        });

        return response()->json([
            'success' => true,
            'data'    => $pets,
        ]);
    }

    public function show($id): JsonResponse
    {
        $pet = Pet::with(['temperamentTags', 'medicalLogs'])->find($id);

        if (! $pet) {
            return response()->json([
                'success' => false,
                'message' => 'Pet not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->transformPet($pet, true),
        ]);
    }

    private function transformPet(Pet $pet, bool $includeDetails = false): array
    {
        $photoUrl = null;
        if ($pet->photo_path) {
            if (str_starts_with($pet->photo_path, 'http')) {
                $photoUrl = $pet->photo_path;
            } else {
                $photoUrl = asset('storage/' . ltrim($pet->photo_path, '/'));
            }
        }

        $data = [
            'id'             => $pet->id,
            'name'           => $pet->name ?: ('Pet #' . $pet->id),
            'type'           => ucfirst($pet->type ?: 'Dog'),
            'breed'          => $pet->breed ?: 'Mixed Breed',
            'color'          => $pet->color ?: 'N/A',
            'gender'         => ucfirst($pet->gender ?: 'Unknown'),
            'age'            => $pet->age ?: 'Unknown',
            'description'    => $pet->description ?: 'No description provided.',
            'image'          => $photoUrl ?: 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=400&q=80',
            'status'         => $pet->status,
            'temperament'    => $pet->temperamentTags->pluck('name')->toArray(),
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
