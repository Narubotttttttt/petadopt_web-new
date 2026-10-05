<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdopterPreference;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class RecommendationApiController extends Controller
{
    /**
     * Get the authenticated adopter's saved preferences.
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['preferences' => null]);
        }

        $preference = AdopterPreference::where('user_id', $user->id)->first();

        return response()->json([
            'preferences' => $preference,
        ]);
    }

    /**
     * Generate pet recommendations using the Python ML Scikit-Learn Engine.
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Resolve Adopter Profile (from request payload or saved database profile)
        $profile = $request->all();

        if (empty($profile) && $user) {
            $saved = AdopterPreference::where('user_id', $user->id)->first();
            if ($saved) {
                $profile = $saved->toArray();
            }
        }

        // Save / update preferences if user is authenticated and sent answers
        if ($user && ! empty($request->input('living_environment'))) {
            AdopterPreference::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'preferred_species'     => $request->input('preferred_species', 'any'),
                    'preferred_gender'      => $request->input('preferred_gender', 'any'),
                    'preferred_age'         => $request->input('preferred_age', 'any'),
                    'preferred_color'       => $request->input('preferred_color'),
                    'living_environment'    => $request->input('living_environment', 'apartment'),
                    'activity_level'        => $request->input('activity_level', 'moderate'),
                    'pet_experience'        => $request->input('pet_experience', 'first_time'),
                    'has_children'          => filter_var($request->input('has_children', false), FILTER_VALIDATE_BOOLEAN),
                    'has_other_pets'        => filter_var($request->input('has_other_pets', false), FILTER_VALIDATE_BOOLEAN),
                    'hours_alone'           => $request->input('hours_alone', '4_7'),
                    'special_care_capacity' => filter_var($request->input('special_care_capacity', false), FILTER_VALIDATE_BOOLEAN),
                    'desired_temperaments'  => $request->input('desired_temperaments', []),
                ]
            );
        }

        $petsQuery = Pet::where('status', 'available');

        // Strict species filtering if user specified 'dog' or 'cat'
        $prefSpecies = strtolower($profile['preferred_species'] ?? $request->input('preferred_species', 'any'));
        if (in_array($prefSpecies, ['dog', 'cat'])) {
            $petsQuery->whereRaw('LOWER(type) = ?', [$prefSpecies]);
        }

        $pets = $petsQuery->with('temperamentTags')
            ->get()
            ->map(function ($p) {
                return [
                    'id'              => $p->id,
                    'name'            => $p->name,
                    'type'            => strtolower($p->type),
                    'breed'           => $p->breed,
                    'age'             => $p->age,
                    'gender'          => $p->gender,
                    'color'           => $p->color,
                    'photo_path'      => $p->photo_path,
                    'medical_history' => $p->medical_history,
                    'description'     => $p->description,
                    'status'          => $p->status,
                    'temperaments'    => $p->temperamentTags->pluck('name')->toArray(),
                ];
            })
            ->toArray();

        if (empty($pets)) {
            return response()->json([
                'success'         => true,
                'algorithm'       => 'Scikit-Learn Random Forest Classifier (Color, Age, Gender Prioritized)',
                'count'           => 0,
                'recommendations' => [],
            ]);
        }

        $payload = [
            'pets'            => $pets,
            'adopter_profile' => $profile,
        ];

        // 3. Execute Python ML Recommender Script via cmd /c for fresh Windows socket context
        $pythonScript  = base_path('ml/run_recommender.py');
        $pythonBin = env('PYTHON_BIN');
        if (! $pythonBin || ! file_exists($pythonBin)) {
            $candidates = [
                base_path('.venv\\Scripts\\python.exe'),
                'C:\\Users\\emili\\AppData\\Local\\Programs\\Python\\Python312\\python.exe',
                'C:\\Program Files\\Python312\\python.exe',
                'python',
            ];
            $pythonBin = 'python';
            foreach ($candidates as $candidate) {
                if ($candidate !== 'python' && file_exists($candidate)) {
                    $pythonBin = $candidate;
                    break;
                }
            }
        }
        $sitePkgs = env('PYTHON_SITE_PACKAGES', '');
        $pythonPath = $sitePkgs ?: '';

        $cmdLine = sprintf(
            'SET PYTHONPATH=%s && SET PYTHONIOENCODING=utf-8 && SET PYTHONASYNCIODEBUG=0 && "%s" -W ignore "%s"',
            $pythonPath,
            $pythonBin,
            $pythonScript
        );

        $process = Process::fromShellCommandline($cmdLine);
        $process->setInput(json_encode($payload));
        $process->setTimeout(15);

        try {
            $process->run();

            if ($process->isSuccessful()) {
                $output = json_decode($process->getOutput(), true);
                if (isset($output['recommendations'])) {
                    // Enrich recommendations with full pet details (photo URLs, etc.)
                    $petMap = Pet::whereIn('id', collect($output['recommendations'])->pluck('pet_id'))
                        ->with('temperamentTags')
                        ->get()
                        ->keyBy('id');

                    foreach ($output['recommendations'] as &$rec) {
                        $petModel = $petMap->get($rec['pet_id']);
                        if ($petModel) {
                            $photoUrl = null;
                            if ($petModel->photo_path) {
                                $photoUrl = str_starts_with($petModel->photo_path, 'http')
                                    ? $petModel->photo_path
                                    : asset('storage/' . ltrim($petModel->photo_path, '/'));
                            }

                            $rec['id'] = $petModel->id;
                            $rec['name'] = $petModel->name ?: ('Pet no. ' . $petModel->id);
                            $rec['type'] = ucfirst($petModel->type ?: 'Dog');
                            $rec['breed'] = $petModel->breed ?: 'Mixed Breed';
                            $rec['color'] = $petModel->color ?: 'N/A';
                            $rec['gender'] = ucfirst($petModel->gender ?: 'Unknown');
                            $rec['age'] = $petModel->age ?: 'Unknown';
                            $rec['description'] = $petModel->description ?: 'Loving rescue pet looking for a forever home.';
                            $rec['medical_history'] = $petModel->medical_history ?: 'No medical history recorded.';
                            $rec['image'] = $photoUrl ?: 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=400&q=80';
                            $rec['photo_url'] = $rec['image'];
                            $rec['status'] = $petModel->status;
                            $rec['temperament'] = $petModel->temperamentTags->pluck('name')->toArray();
                            $rec['temperaments'] = $rec['temperament'];
                        }
                    }

                    return response()->json($output);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Python ML execution error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Python stderr: ' . $process->getErrorOutput());

            return response()->json([
                'success'         => false,
                'algorithm'       => 'Python Scikit-Learn Random Forest Engine',
                'error'           => 'Python ML execution exception: ' . $e->getMessage(),
                'recommendations' => [],
            ], 500);
        }

        \Illuminate\Support\Facades\Log::error('Python ML process failed: ' . $process->getErrorOutput());

        return response()->json([
            'success'         => false,
            'algorithm'       => 'Python Scikit-Learn Random Forest Engine',
            'error'           => 'Python ML process failed: ' . ($process->getErrorOutput() ?: 'Unknown execution error'),
            'recommendations' => [],
        ], 500);
    }
}