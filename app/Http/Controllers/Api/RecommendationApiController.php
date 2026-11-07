<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdopterCompatibilityProfile;
use App\Models\AdopterPreference;
use App\Models\AdoptionApplication;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            return response()->json([
                'preferences' => null,
                'is_locked'   => false,
            ]);
        }

        $preference = AdopterCompatibilityProfile::where('user_id', $user->id)->first();
        $capacity = \App\Models\AdoptionApplication::getAdoptionCapacityDetails($user->email);
        $adoptedCount = $capacity['active_pets_count'];

        $canRetake = false;
        $retakeStatus = 'none';
        $daysRemaining = 0;
        $nextRetakeDate = null;

        if ($preference) {
            $revCount = (int)($preference->revision_count ?? 0);
            $nextAvail = $preference->next_retake_available_at;

            if ($revCount === 0) {
                // Initial completion: 1 immediate grace revision available!
                $canRetake = true;
                $retakeStatus = 'grace_period';
            } elseif ($nextAvail === null || now()->greaterThanOrEqualTo($nextAvail)) {
                // 30-day cooldown passed: monthly update window open!
                $canRetake = true;
                $retakeStatus = 'monthly_available';
            } else {
                // Currently on 30-day cooldown
                $canRetake = false;
                $retakeStatus = 'cooldown';
                $daysRemaining = max(1, (int)now()->diffInDays($nextAvail, false));
                $nextRetakeDate = $nextAvail->format('M d, Y');
            }
        }

        return response()->json([
            'preferences'           => $preference,
            'compatibility_profile' => $preference,
            'is_locked'             => $preference !== null,
            'can_retake'            => $canRetake,
            'retake_status'         => $retakeStatus,
            'days_remaining'        => $daysRemaining,
            'next_retake_date'      => $nextRetakeDate,
            'revision_count'        => (int)($preference?->revision_count ?? 0),
            'adopted_count'          => $adoptedCount,
            'cleared_deceased_count' => $capacity['cleared_deceased_count'],
            'max_pets_allowed'       => 3,
            'remaining_slots'        => $capacity['remaining_slots'],
            'can_adopt_more'         => $capacity['can_adopt_more'],
        ]);
    }

    /**
     * Generate pet recommendations using the Python ML Scikit-Learn Engine.
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?: $request->user();
        if (! $user && $request->bearerToken()) {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken())?->tokenable;
        }

        // 1. Determine if quiz answers are provided in the current request payload
        $hasQuizInPayload = ! empty($request->input('living_environment'))
            || ! empty($request->input('activity_level'))
            || ! empty($request->input('pet_experience'));

        // 2. Check if the authenticated adopter has saved preferences in database
        $saved = null;
        if ($user) {
            $saved = AdopterCompatibilityProfile::where('user_id', $user->id)->first();

            // Business rule: Maximum household adoption limit of 3 active pets per adopter
            $adoptedCount = \App\Models\AdoptionApplication::getActiveAdoptedCount($user->email);

            if ($adoptedCount >= 3) {
                return response()->json([
                    'success'           => true,
                    'is_locked'         => true,
                    'max_limit_reached' => true,
                    'adopted_count'     => $adoptedCount,
                    'count'             => 0,
                    'recommendations'   => [],
                    'message'           => 'You have reached the maximum shelter adoption limit of 3 pets. Your household is currently at full companion capacity.',
                ]);
            }
        }

        // Check cooldown and retake eligibility if adopter already has a profile and submits new quiz answers
        if ($user && $saved && $hasQuizInPayload) {
            $revCount = (int)($saved->revision_count ?? 0);
            $nextAvail = $saved->next_retake_available_at;
            $canRetake = ($revCount === 0) || ($nextAvail === null || now()->greaterThanOrEqualTo($nextAvail));

            if (! $canRetake) {
                $daysRemaining = max(1, (int)now()->diffInDays($nextAvail, false));
                $nextRetakeDate = $nextAvail ? $nextAvail->format('M d, Y') : 'in 30 days';
                return response()->json([
                    'success'        => false,
                    'is_locked'      => true,
                    'can_retake'     => false,
                    'days_remaining' => $daysRemaining,
                    'next_retake_date' => $nextRetakeDate,
                    'message'        => "Your compatibility assessment is locked. Next update will be available on {$nextRetakeDate} ({$daysRemaining} days remaining).",
                ], 403);
            }

            // Update existing profile, increment revision count, and activate 30-day cooldown
            $saved->update([
                'preferred_species'     => $request->input('preferred_species', 'any'),
                'living_environment'    => $request->input('living_environment', 'apartment'),
                'has_yard'              => filter_var($request->input('has_yard', false), FILTER_VALIDATE_BOOLEAN),
                'yard_type'             => $request->input('yard_type'),
                'activity_level'        => $request->input('activity_level', 'moderate'),
                'pet_experience'        => filter_var($request->input('has_other_pets', false), FILTER_VALIDATE_BOOLEAN) ? 'experienced' : $request->input('pet_experience', 'first_time'),
                'has_children'          => filter_var($request->input('has_children', false), FILTER_VALIDATE_BOOLEAN),
                'has_other_pets'        => filter_var($request->input('has_other_pets', false), FILTER_VALIDATE_BOOLEAN),
                'hours_alone'           => $request->input('hours_alone', '4_7'),
                'special_care_capacity' => filter_var($request->input('special_care_capacity', false), FILTER_VALIDATE_BOOLEAN),
                'desired_temperaments'  => $request->input('desired_temperaments', []),
                'revision_count'        => $revCount + 1,
                'next_retake_available_at' => now()->addDays(30),
            ]);

            Cache::forget("recommendations_user_{$user->id}");
        }

        // 3. If the adopter has not answered the quiz yet (neither in request nor in database),
        // return an empty recommendation list so "Recommended for you" remains blank.
        if (! $hasQuizInPayload && ! $saved) {
            return response()->json([
                'success'         => true,
                'has_quiz'        => false,
                'is_locked'       => false,
                'algorithm'       => 'Python Scikit-Learn Random Forest Engine',
                'count'           => 0,
                'recommendations' => [],
                'message'         => 'No quiz answers found. Complete the quiz to see personalized recommendations.',
            ]);
        }

        // 4. Resolve Adopter Profile (from request payload if submitted or saved database profile)
        $profile = $hasQuizInPayload ? $request->all() : ($saved ? $saved->toArray() : []);
        if (!empty($profile['has_other_pets']) && filter_var($profile['has_other_pets'], FILTER_VALIDATE_BOOLEAN)) {
            $profile['pet_experience'] = 'experienced';
        }

        // Save preferences if user is authenticated and submitted answers for the first time
        if ($user && $hasQuizInPayload && ! $saved) {
            $saved = AdopterCompatibilityProfile::create([
                'user_id'               => $user->id,
                'preferred_species'     => $request->input('preferred_species', 'any'),
                'living_environment'    => $request->input('living_environment', 'apartment'),
                'has_yard'              => filter_var($request->input('has_yard', false), FILTER_VALIDATE_BOOLEAN),
                'yard_type'             => $request->input('yard_type'),
                'activity_level'        => $request->input('activity_level', 'moderate'),
                'pet_experience'        => filter_var($request->input('has_other_pets', false), FILTER_VALIDATE_BOOLEAN) ? 'experienced' : $request->input('pet_experience', 'first_time'),
                'has_children'          => filter_var($request->input('has_children', false), FILTER_VALIDATE_BOOLEAN),
                'has_other_pets'        => filter_var($request->input('has_other_pets', false), FILTER_VALIDATE_BOOLEAN),
                'hours_alone'           => $request->input('hours_alone', '4_7'),
                'special_care_capacity' => filter_var($request->input('special_care_capacity', false), FILTER_VALIDATE_BOOLEAN),
                'desired_temperaments'  => $request->input('desired_temperaments', []),
                'revision_count'        => 0,
                'next_retake_available_at' => null,
            ]);
        }

        // Resolve cache key: User ID if authenticated, or MD5 fingerprint of quiz answers for guests
        $cacheKey = $user
            ? "recommendations_user_{$user->id}"
            : (! empty($profile) ? ('recommendations_guest_' . md5(json_encode($profile))) : null);

        $cleanBracketLabels = function ($text) {
            if (!$text || !is_string($text)) return $text;
            $text = preg_replace('/Puspin\s*\((?:Mixed|Mix)\s*Breed\)\s*Cat/i', 'Puspin Cat', $text);
            $text = preg_replace('/Aspin\s*\((?:Mixed|Mix)\s*Breed\)\s*Dog/i', 'Aspin Dog', $text);
            $text = preg_replace('/Puspin\s*\((?:Mixed|Mix)\s*Breed\)/i', 'Puspin', $text);
            $text = preg_replace('/Aspin\s*\((?:Mixed|Mix)\s*Breed\)/i', 'Aspin', $text);
            $text = preg_replace('/\(([^()]*?)\s*\((?:Mixed|Mix)\s*Breed\)\s*([^()]*?)\)/i', '($1 $2)', $text);
            $text = preg_replace('/\(\s*\(([^()]+)\)\s*\)/', '($1)', $text);
            $text = preg_replace('/\(\s+/', '(', $text);
            $text = preg_replace('/\s+\)/', ')', $text);
            $text = preg_replace('/\s{2,}/', ' ', $text);
            return trim($text);
        };

        if ($cacheKey && Cache::has($cacheKey)) {
            $cachedOutput = Cache::get($cacheKey);
            $cachedPetIds = collect($cachedOutput['recommendations'] ?? [])->pluck('id')->filter()->toArray();

            $hasApplied = false;
            if ($user) {
                $hasApplied = AdoptionApplication::where('applicant_email', $user->email)
                    ->whereIn('pet_id', $cachedPetIds)
                    ->whereIn('status', ['pending', 'under_review', 'approved', 'adopted'])
                    ->exists();
            }

            $availableCount = Pet::whereIn('id', $cachedPetIds)->where('status', 'available')->count();
            if (!empty($cachedPetIds) && ! $hasApplied && $availableCount === count($cachedPetIds)) {
                if (isset($cachedOutput['comparative_analysis'])) {
                    if (isset($cachedOutput['comparative_analysis']['summary'])) {
                        $cachedOutput['comparative_analysis']['summary'] = $cleanBracketLabels($cachedOutput['comparative_analysis']['summary']);
                    }
                    if (isset($cachedOutput['comparative_analysis']['verdict'])) {
                        $cachedOutput['comparative_analysis']['verdict'] = $cleanBracketLabels($cachedOutput['comparative_analysis']['verdict']);
                    }
                }
                if (!empty($cachedOutput['recommendations'])) {
                    foreach ($cachedOutput['recommendations'] as &$r) {
                        if (!empty($r['reasons']) && is_array($r['reasons'])) {
                            $r['reasons'] = array_map($cleanBracketLabels, $r['reasons']);
                        }
                        if (!empty($r['match_reasons']) && is_array($r['match_reasons'])) {
                            $r['match_reasons'] = array_map($cleanBracketLabels, $r['match_reasons']);
                        }
                    }
                    unset($r);
                }
                return response()->json($cachedOutput);
            }
            Cache::forget($cacheKey);
        }

        $petsQuery = Pet::where('status', 'available');

        // Exclude pets that this adopter has already applied for or adopted
        $appliedPetIds = [];
        if ($user) {
            $appliedPetIds = AdoptionApplication::where('applicant_email', $user->email)
                ->whereIn('status', ['pending', 'under_review', 'approved', 'adopted'])
                ->pluck('pet_id')
                ->filter()
                ->unique()
                ->toArray();

            if (!empty($appliedPetIds)) {
                $petsQuery->whereNotIn('id', $appliedPetIds);
            }
        }

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
                    'size'            => strtolower($p->size ?? 'medium'),
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
                'is_locked'       => ($user && $saved !== null),
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
                'C:\\Python314\\python.exe',
                'C:\\Program Files\\Python314\\python.exe',
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

        $processEnv = array_merge($_SERVER, [
            'SystemRoot'         => getenv('SystemRoot') ?: 'C:\\Windows',
            'windir'             => getenv('windir') ?: 'C:\\Windows',
            'PATH'               => getenv('PATH') ?: (isset($_SERVER['PATH']) ? $_SERVER['PATH'] : ''),
            'PYTHONPATH'         => $pythonPath,
            'PYTHONIOENCODING'   => 'utf-8',
            'PYTHONASYNCIODEBUG' => '0',
            'GEMINI_API_KEY'     => env('GEMINI_API_KEY', ''),
        ]);

        $process = new Process([$pythonBin, '-W', 'ignore', $pythonScript], base_path(), $processEnv);
        $process->setInput(json_encode($payload));
        $process->setTimeout(25);

        try {
            $process->run();

            if ($process->isSuccessful()) {
                $output = json_decode($process->getOutput(), true);
                if (isset($output['recommendations'])) {
                    // Enrich recommendations with full pet details (photo URLs, etc.)
                    // Strictly enforce status 'available' (ready for adoption)
                    $petMap = Pet::whereIn('id', collect($output['recommendations'])->pluck('pet_id'))
                        ->where('status', 'available')
                        ->with('temperamentTags')
                        ->get()
                        ->keyBy('id');

                    $validRecs = [];
                    foreach ($output['recommendations'] as $rec) {
                        $petModel = $petMap->get($rec['pet_id']);
                        if (! $petModel || strtolower($petModel->status) !== 'available' || in_array($rec['pet_id'], $appliedPetIds)) {
                            continue;
                        }

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
                        $rec['status'] = 'available';
                        $rec['temperament'] = $petModel->temperamentTags->pluck('name')->toArray();
                        $rec['temperaments'] = $rec['temperament'];

                        $rawPct = $rec['match_percentage'] ?? $rec['match_score'] ?? 80;
                        $numPct = is_numeric($rawPct) ? (float)$rawPct : (float)str_replace('%', '', (string)$rawPct);
                        $rec['match_percentage'] = $numPct;
                        $rec['compatibility_score'] = $numPct;

                        $recReasons = $rec['match_reasons'] ?? $rec['reasons'] ?? [];
                        if (empty($recReasons)) {
                            $recReasons = ['Strong overall behavioral and lifestyle compatibility'];
                        }
                        $rec['match_reasons'] = $recReasons;
                        $rec['reasons'] = $recReasons;
                        $rec['compatibility_breakdown'] = $rec['compatibility_breakdown'] ?? ['space' => 88, 'routine' => 85, 'temperament' => 90];
                        $rec['care_tip'] = $rec['care_tip'] ?? $rec['consideration'] ?? 'Establish consistent daily routines during initial transition.';
                        $rec['consideration'] = $rec['care_tip'];
                        $rec['comparison_traits'] = $rec['comparison_traits'] ?? [
                            'energy_level'          => 'Moderate',
                            'space_fit'             => 'Apartment Friendly',
                            'alone_tolerance'       => 'Moderate',
                            'social_compatibility' => 'Friendly with pets',
                            'grooming_care'         => 'Low maintenance',
                        ];

                        $validRecs[] = $rec;
                    }

                    // Enforce 80% minimum compatibility threshold and Top 3 limit strictly from available pets
                    $highMatchRecs = array_values(array_filter($validRecs, function ($rec) {
                        return ($rec['match_percentage'] ?? 0) >= 80.0;
                    }));

                    $output['recommendations'] = array_slice($highMatchRecs, 0, 3);
                    $output['count'] = count($output['recommendations']);
                    $output['is_locked'] = ($user && $saved !== null);
                    $output['can_retake'] = $saved ? ((int)($saved->revision_count ?? 0) === 0 || ($saved->next_retake_available_at === null || now()->greaterThanOrEqualTo($saved->next_retake_available_at))) : true;
                    $output['revision_count'] = (int)($saved?->revision_count ?? 0);
                    $output['adopted_count'] = $adoptedCount ?? 0;
                    $output['max_pets_allowed'] = 3;
                    $output['remaining_slots'] = max(0, 3 - ($adoptedCount ?? 0));

                    if (!isset($output['comparative_analysis']) || empty($output['comparative_analysis']['summary'])) {
                        $p1 = $output['recommendations'][0] ?? null;
                        $p1Name = $p1['name'] ?? 'Your top candidate';
                        $p1Score = $p1['match_percentage'] ?? 80;
                        $output['comparative_analysis'] = [
                            'summary' => "{$p1Name} ({$p1Score}% Match) demonstrates the strongest behavioral and schedule compatibility for your household, while other matched candidates provide great alternative options.",
                            'verdict' => "{$p1Name} is your recommended primary companion.",
                        ];
                    }

                    if (isset($output['comparative_analysis'])) {
                        if (isset($output['comparative_analysis']['summary'])) {
                            $output['comparative_analysis']['summary'] = $cleanBracketLabels($output['comparative_analysis']['summary']);
                        }
                        if (isset($output['comparative_analysis']['verdict'])) {
                            $output['comparative_analysis']['verdict'] = $cleanBracketLabels($output['comparative_analysis']['verdict']);
                        }
                    }
                    if (!empty($output['recommendations'])) {
                        foreach ($output['recommendations'] as &$r) {
                            if (!empty($r['reasons']) && is_array($r['reasons'])) {
                                $r['reasons'] = array_map($cleanBracketLabels, $r['reasons']);
                            }
                            if (!empty($r['match_reasons']) && is_array($r['match_reasons'])) {
                                $r['match_reasons'] = array_map($cleanBracketLabels, $r['match_reasons']);
                            }
                        }
                        unset($r);
                    }

                    if ($cacheKey && !empty($output['recommendations'])) {
                        Cache::put($cacheKey, $output, now()->addHours(2));
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