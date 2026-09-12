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
        $pythonBin     = env('PYTHON_BIN', 'C:\\Python314\\python.exe');
        $sitePkgs      = env('PYTHON_SITE_PACKAGES',
                             'C:\\Users\\Junar\\AppData\\Roaming\\Python\\Python314\\site-packages');
        $pythonPath    = $sitePkgs . ';C:\\Python314\\Lib\\site-packages';

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
        }

        // Log why Python failed
        if (!$process->isSuccessful()) {
            \Illuminate\Support\Facades\Log::warning('Python process failed. Exit code: ' . $process->getExitCode());
            \Illuminate\Support\Facades\Log::warning('Python stderr: ' . $process->getErrorOutput());
        }

        // 4. Built-in Native Multi-Attribute Engine Fallback (Ensures 100% Zero-Downtime)
        $fallbackRecs = $this->nativeCosineSimilarityMatch($pets, $profile);

        return response()->json([
            'success'         => true,
            'algorithm'       => 'Multi-Attribute Scoring Engine (Color, Age, Gender Prioritized Native Fallback)',
            'count'           => count($fallbackRecs),
            'recommendations' => $fallbackRecs,
        ]);
    }

    /**
     * High-performance Native Multi-Attribute & Vector Cosine Similarity engine (Fallback).
     * Enforces: Color (22%) + Age (20%) + Gender (20%) = 62% > Temperament (19%) + Capability (19%) = 38%
     */
    private function nativeCosineSimilarityMatch(array $pets, array $profile): array
    {
        $allTags = ['Friendly', 'Calm', 'Energetic', 'Shy', 'Playful', 'Independent', 'Affectionate', 'Protective'];
        $desiredTags = $profile['desired_temperaments'] ?? ['Friendly', 'Calm'];
        if (empty($desiredTags)) {
            $desiredTags = ['Friendly', 'Calm'];
        }

        $prefSpecies = strtolower($profile['preferred_species'] ?? 'any');
        if (in_array($prefSpecies, ['dog', 'cat'])) {
            $pets = array_values(array_filter($pets, fn($p) => strtolower($p['type'] ?? '') === $prefSpecies));
        }

        $prefColor = strtolower(trim($profile['preferred_color'] ?? 'any'));
        $prefAge = strtolower(trim($profile['preferred_age'] ?? 'any'));
        $prefGender = strtolower(trim($profile['preferred_gender'] ?? 'any'));

        $colorFamilies = [
            'orange'   => ['orange', 'ginger', 'red', 'yellow', 'gold'],
            'black'    => ['black', 'dark', 'charcoal'],
            'white'    => ['white', 'cream', 'ivory', 'light'],
            'brown'    => ['brown', 'tan', 'chocolate', 'fawn', 'buff'],
            'tricolor' => ['tricolor', 'calico', 'tortie', 'torbie', 'multi'],
            'gray'     => ['gray', 'grey', 'silver', 'blue', 'smoke'],
            'tabby'    => ['tabby', 'striped', 'brindle', 'tiger'],
        ];

        // Adopter temperament vector
        $userVec = array_map(fn($t) => in_array($t, $desiredTags) ? 1.0 : 0.0, $allTags);
        $userMagnitude = sqrt(array_sum(array_map(fn($v) => $v * $v, $userVec))) ?: 1.0;

        $results = [];
        foreach ($pets as $pet) {
            $petTags = $pet['temperaments'] ?? [];
            $petVec = array_map(fn($t) => in_array($t, $petTags) ? 1.0 : 0.0, $allTags);
            $petMagnitude = sqrt(array_sum(array_map(fn($v) => $v * $v, $petVec))) ?: 1.0;

            // 1. Temperament Cosine Similarity
            $dotProduct = 0;
            for ($i = 0; $i < count($allTags); $i++) {
                $dotProduct += ($userVec[$i] * $petVec[$i]);
            }
            $cosineSim = count($petTags) > 0 ? ($dotProduct / ($userMagnitude * $petMagnitude)) : 0.5;

            // 2. Color Match (22%)
            $colorScore = 1.0;
            $petColor = strtolower(trim($pet['color'] ?? ''));
            if (!in_array($prefColor, ['any', 'all', '', 'any color'])) {
                $colorScore = 0.0;
                if (str_contains($petColor, $prefColor) || str_contains($prefColor, $petColor)) {
                    $colorScore = 1.0;
                } else {
                    foreach ($colorFamilies as $fam => $aliases) {
                        $prefInFam = str_contains($prefColor, $fam) || collect($aliases)->contains(fn($a) => str_contains($prefColor, $a));
                        $petInFam = str_contains($petColor, $fam) || collect($aliases)->contains(fn($a) => str_contains($petColor, $a));
                        if ($prefInFam && $petInFam) {
                            $colorScore = 1.0;
                            break;
                        }
                    }
                }
            }

            // 3. Age Match (20%)
            $ageScore = 1.0;
            $petAge = strtolower(trim($pet['age'] ?? ''));
            if (!in_array($prefAge, ['any', 'all', '', 'any age'])) {
                $ageScore = 0.0;
                $isPuppyKitten = str_contains($petAge, 'puppy') || str_contains($petAge, 'kitten') || str_contains($petAge, 'month') || str_contains($petAge, '< 1');
                $isYoung = str_contains($petAge, 'young') || str_contains($petAge, '1 year') || str_contains($petAge, '2 year');
                $isSenior = str_contains($petAge, 'senior') || str_contains($petAge, 'old') || str_contains($petAge, '8') || str_contains($petAge, '9') || str_contains($petAge, '10');
                $isAdult = !$isPuppyKitten && !$isYoung && !$isSenior;

                if (str_contains($prefAge, 'puppy') || str_contains($prefAge, 'kitten')) {
                    $ageScore = $isPuppyKitten ? 1.0 : ($isYoung ? 0.4 : 0.0);
                } elseif (str_contains($prefAge, 'young')) {
                    $ageScore = $isYoung ? 1.0 : (($isPuppyKitten || $isAdult) ? 0.4 : 0.0);
                } elseif (str_contains($prefAge, 'senior')) {
                    $ageScore = $isSenior ? 1.0 : ($isAdult ? 0.4 : 0.0);
                } else {
                    $ageScore = $isAdult ? 1.0 : (($isYoung || $isSenior) ? 0.4 : 0.0);
                }
            }

            // 4. Gender Match (20%)
            $genderScore = 1.0;
            $petGender = strtolower(trim($pet['gender'] ?? ''));
            if (!in_array($prefGender, ['any', 'all', '', 'any gender'])) {
                if ($prefGender === 'male') {
                    $genderScore = (str_contains($petGender, 'male') && !str_contains($petGender, 'female')) ? 1.0 : 0.0;
                } elseif ($prefGender === 'female') {
                    $genderScore = str_contains($petGender, 'female') ? 1.0 : 0.0;
                }
            }

            // 5. Lifestyle & Capability Match (19%)
            $lifestylePts = 0.60;
            $livingEnv = strtolower($profile['living_environment'] ?? 'apartment');
            if (str_contains($livingEnv, 'apartment')) {
                if (in_array('Calm', $petTags) || in_array('Independent', $petTags) || strtolower($pet['type'] ?? '') === 'cat') {
                    $lifestylePts += 0.20;
                } elseif (strtolower($pet['type'] ?? '') === 'dog' && in_array('Energetic', $petTags)) {
                    $lifestylePts -= 0.20;
                }
            } else {
                $lifestylePts += 0.20;
            }

            $activityLevel = strtolower($profile['activity_level'] ?? 'moderate');
            if (in_array($activityLevel, ['high', 'active']) && (in_array('Energetic', $petTags) || in_array('Playful', $petTags))) {
                $lifestylePts += 0.15;
            } elseif ($activityLevel === 'relaxed' && in_array('Calm', $petTags)) {
                $lifestylePts += 0.15;
            }

            $lifestyleNormalized = min(1.0, max(0.0, $lifestylePts));

            // Composite Weighted Score: Color (22%) + Age (20%) + Gender (20%) + Temperament (19%) + Capability (19%)
            $finalScore = (0.22 * $colorScore) + (0.20 * $ageScore) + (0.20 * $genderScore) + (0.19 * $cosineSim) + (0.19 * $lifestyleNormalized);
            $matchPct = round(min(99.0, max(50.0, ($finalScore * 48.0) + 51.0)), 1);

            // Prioritized Match Reasons
            $reasons = [];
            if (!in_array($prefColor, ['any', 'all', '', 'any color']) && $colorScore >= 0.75) {
                $reasons[] = 'Matches your preferred ' . ($pet['color'] ?: 'coat') . ' color';
            }
            if (!in_array($prefAge, ['any', 'all', '', 'any age']) && $ageScore >= 0.75) {
                $reasons[] = 'Matches your desired ' . ($pet['age'] ?: 'age') . ' group';
            }
            if (!in_array($prefGender, ['any', 'all', '', 'any gender']) && $genderScore >= 0.75) {
                $reasons[] = 'Matches your preferred ' . ucfirst($pet['gender'] ?: 'gender') . ' gender';
            }
            if ($cosineSim >= 0.65) {
                $reasons[] = 'Shares your preferred temperament traits';
            }
            if (str_contains($livingEnv, 'apartment') && (in_array('Calm', $petTags) || strtolower($pet['type'] ?? '') === 'cat')) {
                $reasons[] = 'Well-suited for apartment or indoor living';
            } elseif (!str_contains($livingEnv, 'apartment')) {
                $reasons[] = 'Great match for your home living space';
            }

            $results[] = [
                'pet_id'                 => $pet['id'],
                'name'                   => $pet['name'],
                'type'                   => $pet['type'],
                'breed'                  => $pet['breed'],
                'age'                    => $pet['age'],
                'gender'                 => $pet['gender'],
                'color'                  => $pet['color'],
                'photo_path'             => $pet['photo_path'],
                'status'                 => $pet['status'],
                'match_percentage'       => $matchPct,
                'temperament_similarity' => round($cosineSim * 100, 1),
                'temperaments'           => $petTags,
                'match_reasons'          => array_slice($reasons ?: ['Good overall compatibility with your profile'], 0, 3),
            ];
        }

        usort($results, fn($a, $b) => $b['match_percentage'] <=> $a['match_percentage']);

        return $results;
    }
}