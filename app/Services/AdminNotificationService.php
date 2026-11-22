<?php

namespace App\Services;

use App\Models\AdoptionApplication;
use App\Models\PetHealthUpdate;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class AdminNotificationService
{
    public static function getNotifications(): array
    {
        $rawNotifications = Cache::remember('admin_notifications_raw', 15, function () {
            return self::buildNotifications();
        });

        $userId = auth()->id();
        $cacheKey = $userId ? 'admin_read_notifications_' . $userId : 'admin_read_notifications_guest';
        $sessionRead = Session::get('admin_read_notifications', []);
        $cachedRead  = Cache::get($cacheKey, []);
        $readIds     = array_unique(array_merge($sessionRead, $cachedRead));

        $notifications = array_map(function ($n) use ($readIds) {
            $n['is_read'] = in_array($n['id'], $readIds);
            return $n;
        }, $rawNotifications);

        $unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));

        return [
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'counts' => [
                'all' => count($notifications),
                'checkins' => count(array_filter($notifications, fn($n) => $n['category'] === 'checkin')),
                'overdue' => count(array_filter($notifications, fn($n) => $n['category'] === 'overdue')),
                'requests' => count(array_filter($notifications, fn($n) => $n['category'] === 'request')),
            ]
        ];
    }

    private static function buildNotifications(): array
    {
        $notifications = [];

        // 1. Group check-ins and overdue alerts by Adopter
        $approvedApps = AdoptionApplication::with([
            'pet.healthUpdates' => function($q) {
                $q->latest('check_in_date');
            }
        ])->where('status', 'approved')->get();

        $allEmails = $approvedApps->pluck('applicant_email')->filter()->unique()->toArray();
        $userMap = User::whereIn('email', $allEmails)->get()->keyBy(function($u) {
            return strtolower(trim($u->email));
        });

        $grouped = $approvedApps->groupBy(function($app) {
            return strtolower(trim($app->applicant_email ?: $app->applicant_name));
        });

        foreach ($grouped as $emailKey => $apps) {
            $primary = $apps->first();
            $user = $userMap->get($emailKey);
            $adopterName = $primary->applicant_name;
            $adopterIdCode = $user ? sprintf('ADP-%04d', $user->id) : sprintf('APP-%04d', $primary->id);
            
            $petDetails = [];
            $hasRecentCheckin = false;
            $hasOverdue = false;
            $hasDeceased = false;
            $latestTimestamp = 0;
            
            foreach ($apps as $app) {
                $pet = $app->pet;
                if (!$pet) continue;
                
                $petName = $pet->name ?: 'Pet no. ' . $pet->id;
                $latestUpdate = $pet->healthUpdates->first();
                $isPetDeceased = ($pet->status === 'deceased') || ($latestUpdate && $latestUpdate->health_status === 'deceased');
                
                // Exclude deceased pets from routine check-in notifications (handled in dedicated memorial notifications)
                if ($isPetDeceased) {
                    continue;
                }

                if ($latestUpdate) {
                    $updateTime = $latestUpdate->created_at ? $latestUpdate->created_at->timestamp : 0;
                    if ($updateTime > $latestTimestamp) {
                        $latestTimestamp = $updateTime;
                    }

                    $healthLabel = match($latestUpdate->health_status) {
                        'healthy' => 'Healthy & Active',
                        'minor_issue' => 'Minor Issue',
                        'under_treatment' => 'Under Treatment',
                        default => ucfirst(str_replace('_', ' ', $latestUpdate->health_status ?? 'Healthy'))
                    };
                    
                    // Check if older than 30 days
                    $lastDate = $latestUpdate->check_in_date ?? $latestUpdate->created_at;
                    $carbonLastDate = Carbon::parse($lastDate);
                    $nextDue = $carbonLastDate->copy()->addDays(30);
                    
                    if (now()->greaterThan($nextDue)) {
                        $daysOverdue = (int) $nextDue->diffInDays(now());
                        $hasOverdue = true;
                        $petDetails[] = [
                            'pet_name' => $petName,
                            'status_type' => 'overdue',
                            'status_label' => "Overdue ({$daysOverdue}d)",
                            'is_alert' => true,
                            'time' => $latestUpdate->created_at ? $latestUpdate->created_at->diffForHumans() : '',
                        ];
                    } else {
                        $hasRecentCheckin = true;
                        $petDetails[] = [
                            'pet_name' => $petName,
                            'status_type' => 'submitted',
                            'status_label' => $healthLabel,
                            'is_alert' => false,
                            'time' => $latestUpdate->created_at ? $latestUpdate->created_at->diffForHumans() : '',
                        ];
                    }
                } else {
                    if ($isPetDeceased) {
                        continue;
                    }
                    // No health updates yet
                    $approvedDate = $app->approved_at ?? $app->updated_at;
                    if ($approvedDate) {
                        $nextDue = Carbon::parse($approvedDate)->addDays(30);
                        if (now()->greaterThan($nextDue)) {
                            $daysOverdue = (int) $nextDue->diffInDays(now());
                            $hasOverdue = true;
                            $petDetails[] = [
                                'pet_name' => $petName,
                                'status_type' => 'overdue',
                                'status_label' => "Overdue ({$daysOverdue}d)",
                                'is_alert' => true,
                                'time' => 'Never updated',
                            ];
                        } else {
                            $daysLeft = (int) now()->diffInDays($nextDue);
                            $petDetails[] = [
                                'pet_name' => $petName,
                                'status_type' => 'pending_first',
                                'status_label' => "Due in {$daysLeft}d",
                                'is_alert' => false,
                                'time' => 'Due ' . $nextDue->format('M d'),
                            ];
                        }
                    }
                }
            }
            
            if (!empty($petDetails) && ($hasRecentCheckin || $hasOverdue || $hasDeceased)) {
                $id = 'adopter_' . md5($emailKey . '_' . $latestTimestamp);
                $category = $hasOverdue ? 'overdue' : 'checkin';
                $severity = $hasDeceased ? 'danger' : ($hasOverdue ? 'danger' : 'success');
                $title = $hasDeceased 
                    ? 'Pet Life Event: Passing Notification' 
                    : ($hasOverdue ? '30-Day Check-in Overdue' : 'Monthly Health Check-in');
                
                $adopterNameParts = preg_split('/\s+/', trim($adopterName));
                $adopterInitials = count($adopterNameParts) >= 2 
                    ? strtoupper(mb_substr($adopterNameParts[0], 0, 1) . mb_substr(end($adopterNameParts), 0, 1))
                    : strtoupper(mb_substr($adopterName, 0, 1));

                $targetUrl = $hasDeceased 
                    ? url('/adopters/reports') . '?status=deceased&search=' . urlencode($adopterName)
                    : url('/adopters') . '?search=' . urlencode($adopterName);

                $firstPetName = $petDetails[0]['pet_name'] ?? 'their pet';
                if ($hasDeceased) {
                    $deceasedPet = collect($petDetails)->firstWhere('status_type', 'deceased');
                    $deceasedPetName = $deceasedPet['pet_name'] ?? $firstPetName;
                    $message = "{$adopterName} reported that {$deceasedPetName} has passed away. Memorial record and adopter notes logged for shelter review.";
                } elseif ($hasOverdue) {
                    $overduePet = collect($petDetails)->firstWhere('status_type', 'overdue');
                    $overduePetName = $overduePet['pet_name'] ?? $firstPetName;
                    $message = "{$adopterName} has an overdue monthly update for {$overduePetName}.";
                } else {
                    $message = "{$adopterName} submitted a monthly health and photo update for {$firstPetName}.";
                }

                $notifications[] = [
                    'id' => $id,
                    'category' => $category,
                    'severity' => $severity,
                    'type' => 'adopter_checkin_group',
                    'adopter_name' => $adopterName,
                    'adopter_id_code' => $adopterIdCode,
                    'avatar' => $user->avatar_url ?? null,
                    'initials' => $adopterInitials,
                    'title' => $title,
                    'message' => $message,
                    'body' => $message,
                    'pets_count' => count($apps),
                    'pet_details' => $petDetails,
                    'time' => $latestTimestamp ? Carbon::createFromTimestamp($latestTimestamp)->diffForHumans() : 'Recently',
                    'is_read' => false,
                    'url' => $targetUrl,
                    'action_url' => $targetUrl,
                    'action_hint' => $hasDeceased ? 'View Memorial Report' : 'View adopter profile & check-in history',
                    'created_at' => $latestTimestamp ?: now()->timestamp,
                    'is_today' => $latestTimestamp ? ((now()->timestamp - $latestTimestamp) < 86400) : true,
                ];
            }
        }

        // 2. Pending Adoption Applications
        $pendingApps = AdoptionApplication::with('pet')->where('status', 'pending')->latest()->take(10)->get();
        foreach ($pendingApps as $pApp) {
            $petName = $pApp->pet ? ($pApp->pet->name ?: 'Pet no. ' . $pApp->pet_id) : 'Pet no. ' . $pApp->pet_id;
            $id = 'app_' . $pApp->id;

            $applicantParts = preg_split('/\s+/', trim($pApp->applicant_name));
            $applicantInitials = count($applicantParts) >= 2 
                ? strtoupper(mb_substr($applicantParts[0], 0, 1) . mb_substr(end($applicantParts), 0, 1))
                : strtoupper(mb_substr($pApp->applicant_name, 0, 1));

            $message = "{$pApp->applicant_name} submitted an adoption application for {$petName}.";

            $notifications[] = [
                'id' => $id,
                'category' => 'request',
                'severity' => 'info',
                'type' => 'application_pending',
                'adopter_name' => $pApp->applicant_name,
                'adopter_id_code' => 'APP-' . sprintf('%04d', $pApp->id),
                'avatar' => null,
                'initials' => $applicantInitials,
                'title' => "New Adoption Application",
                'message' => $message,
                'body' => $message,
                'pets_count' => 1,
                'pet_details' => [
                    [
                        'pet_name' => $petName,
                        'status_type' => 'pending_request',
                        'status_label' => 'Awaiting Review',
                        'is_alert' => false,
                        'time' => $pApp->created_at ? $pApp->created_at->diffForHumans() : 'Recently',
                    ]
                ],
                'time' => $pApp->created_at ? $pApp->created_at->diffForHumans() : 'Recently',
                'is_read' => false,
                'url' => url('/adoption-applications/' . $pApp->id),
                'action_url' => url('/adoption-applications/' . $pApp->id),
                'action_hint' => 'Review submitted application & applicant details',
                'created_at' => $pApp->created_at ? $pApp->created_at->timestamp : 0,
                'is_today' => $pApp->created_at ? ((now()->timestamp - $pApp->created_at->timestamp) < 86400) : false,
            ];
        }

        // 3. Pending External Veterinary Vaccination Records
        $pendingVaccinations = \App\Models\MedicalLog::with(['pet', 'creator'])
            ->where('is_external', true)
            ->where('verification_status', 'pending')
            ->latest('created_at')
            ->take(15)
            ->get();

        foreach ($pendingVaccinations as $vLog) {
            $petName = $vLog->pet ? ($vLog->pet->name ?: 'Pet no. ' . $vLog->pet_id) : 'Pet no. ' . $vLog->pet_id;
            $adopter = $vLog->creator;
            $adopterName = $adopter ? $adopter->name : 'Adopter';
            $adopterIdCode = $adopter ? sprintf('ADP-%04d', $adopter->id) : 'ADP';
            $id = 'med_log_' . $vLog->id;

            $adopterParts = preg_split('/\s+/', trim($adopterName));
            $adopterInitials = count($adopterParts) >= 2 
                ? strtoupper(mb_substr($adopterParts[0], 0, 1) . mb_substr(end($adopterParts), 0, 1))
                : strtoupper(mb_substr($adopterName, 0, 1));

            $vaccineDetail = !empty($vLog->vaccine_name) ? $vLog->vaccine_name : ucfirst($vLog->category);

            $actionUrl = url('/adopters/reports') . '?tab=vet_proofs&verify_log_id=' . $vLog->id;
            $message = "{$adopterName} uploaded an external vaccination record for {$petName} ({$vaccineDetail}). Verification needed.";

            $notifications[] = [
                'id'              => $id,
                'category'        => 'checkin',
                'severity'        => 'warning',
                'type'            => 'vaccination_proof_pending',
                'adopter_name'    => $adopterName,
                'adopter_id_code' => $adopterIdCode,
                'avatar'          => $adopter?->avatar_url,
                'initials'        => $adopterInitials,
                'title'           => 'New Vaccination Record Submitted',
                'message'         => $message,
                'body'            => $message,
                'pets_count'      => 1,
                'pet_details'     => [
                    [
                        'pet_name'     => $petName,
                        'status_type'  => 'vaccination_pending',
                        'status_label' => "{$vaccineDetail} Proof Verification",
                        'is_alert'     => true,
                        'time'         => $vLog->created_at ? $vLog->created_at->diffForHumans() : 'Recently',
                    ]
                ],
                'time'            => $vLog->created_at ? $vLog->created_at->diffForHumans() : 'Recently',
                'is_read'         => false,
                'url'             => $actionUrl,
                'action_url'      => $actionUrl,
                'action_hint'     => 'Review Proof',
                'created_at'      => $vLog->created_at ? $vLog->created_at->timestamp : now()->timestamp,
                'is_today'        => $vLog->created_at ? ((now()->timestamp - $vLog->created_at->timestamp) < 86400) : true,
            ];
        }

        // 4. Pet Passing & Memorial Dedication Submissions
        $deceasedHealthUpdates = PetHealthUpdate::with(['pet', 'user.adoptersProfile', 'application'])
            ->where('health_status', 'deceased')
            ->latest('created_at')
            ->take(20)
            ->get();

        foreach ($deceasedHealthUpdates as $dUpdate) {
            $pet = $dUpdate->pet;
            $petName = $pet?->name ?: ('Pet no. ' . $dUpdate->pet_id);
            $user = $dUpdate->user;
            $adopterProfile = $user?->adoptersProfile;
            $adopterName = $adopterProfile?->full_name ?? ($user?->name ?? ($dUpdate->application?->applicant_name ?? 'Adopter'));
            $adopterIdCode = $adopterProfile?->adopter_code ?? ($user ? sprintf('ADP-%04d', $user->id) : 'ADP');
            $isPendingReview = empty($dUpdate->review_decision) || in_array($dUpdate->review_decision, ['pending', 'investigation_pending']);
            $statusLabel = 'Awaiting Shelter Review';
            if ($dUpdate->review_decision === 'investigation_pending') {
                $statusLabel = 'On-Site Visit Requested';
            } elseif (!empty($dUpdate->review_decision) && $dUpdate->review_decision !== 'pending') {
                $statusLabel = ucfirst(str_replace('_', ' ', $dUpdate->review_decision));
            }

            $adopterParts = preg_split('/\s+/', trim($adopterName));
            $adopterInitials = count($adopterParts) >= 2 
                ? strtoupper(mb_substr($adopterParts[0], 0, 1) . mb_substr(end($adopterParts), 0, 1))
                : strtoupper(mb_substr($adopterName, 0, 1));

            $actionUrl = url('/adopters/reports') . '?tab=pet_dedications&search=' . urlencode($petName);
            $message = "{$adopterName} submitted a pet passing report and memorial dedication for {$petName}. Shelter review is required to verify natural causes or record welfare findings.";

            $notifications[] = [
                'id'              => 'dedication_' . $dUpdate->id,
                'category'        => 'request',
                'severity'        => 'danger',
                'type'            => 'pet_dedication_pending',
                'adopter_name'    => $adopterName,
                'adopter_id_code' => $adopterIdCode,
                'avatar'          => $user?->avatar_url,
                'initials'        => $adopterInitials,
                'title'           => 'Pet Passing Notice & Memorial Dedication',
                'message'         => $message,
                'body'            => $message,
                'pets_count'      => 1,
                'pet_details'     => [
                    [
                        'pet_name'     => $petName,
                        'status_type'  => 'deceased',
                        'status_label' => $statusLabel,
                        'is_alert'     => $isPendingReview,
                        'time'         => $dUpdate->created_at ? $dUpdate->created_at->diffForHumans() : 'Recently',
                    ]
                ],
                'time'            => $dUpdate->created_at ? $dUpdate->created_at->diffForHumans() : 'Recently',
                'is_read'         => false,
                'url'             => $actionUrl,
                'action_url'      => $actionUrl,
                'action_hint'     => 'Review Dedication & Determine Standing',
                'created_at'      => $dUpdate->created_at ? $dUpdate->created_at->timestamp : now()->timestamp,
                'is_today'        => $dUpdate->created_at ? ((now()->timestamp - $dUpdate->created_at->timestamp) < 86400) : true,
            ];
        }

        // Sort all by created_at desc
        usort($notifications, fn($a, $b) => $b['created_at'] <=> $a['created_at']);

        return $notifications;
    }

    public static function clearCache(): void
    {
        Cache::forget('admin_notifications_raw');
    }

    public static function markAllRead(): void
    {
        $data = self::getNotifications();
        $allIds = array_column($data['notifications'], 'id');

        $userId = auth()->id();
        $cacheKey = $userId ? 'admin_read_notifications_' . $userId : 'admin_read_notifications_guest';
        $sessionRead = Session::get('admin_read_notifications', []);
        $cachedRead  = Cache::get($cacheKey, []);
        $merged = array_values(array_unique(array_merge($sessionRead, $cachedRead, $allIds)));

        Session::put('admin_read_notifications', $merged);
        Session::save();

        Cache::put($cacheKey, $merged, now()->addDays(60));
    }

    public static function markAsRead(string $id): void
    {
        $readIds = Session::get('admin_read_notifications', []);
        if (!in_array($id, $readIds)) {
            $readIds[] = $id;
            Session::put('admin_read_notifications', $readIds);
            Session::save();
        }

        $userId = auth()->id();
        $cacheKey = $userId ? 'admin_read_notifications_' . $userId : 'admin_read_notifications_guest';
        $cached = Cache::get($cacheKey, []);
        if (!in_array($id, $cached)) {
            $cached[] = $id;
            Cache::put($cacheKey, $cached, now()->addDays(60));
        }
    }

    public static function markAdoptionRequestsViewed(): void
    {
        $maxId = AdoptionApplication::max('id') ?? 0;
        Session::put('admin_viewed_adoption_requests_max_id', $maxId);
        Session::save();

        if ($userId = auth()->id()) {
            Cache::put('admin_viewed_adoption_requests_max_id_' . $userId, $maxId, now()->addDays(60));
        }

        // Also mark individual pending app notifications as read in notification bell
        $pendingAppIds = AdoptionApplication::whereIn('status', ['pending', 'under_review'])->pluck('id');
        foreach ($pendingAppIds as $pId) {
            self::markAsRead('app_' . $pId);
        }
    }

    public static function getUnviewedAdoptionRequestsCount(): int
    {
        $userId = auth()->id();
        $maxId = Session::get('admin_viewed_adoption_requests_max_id');
        if ($maxId === null && $userId) {
            $maxId = Cache::get('admin_viewed_adoption_requests_max_id_' . $userId);
        }

        $query = AdoptionApplication::whereIn('status', ['pending', 'under_review']);

        if ($maxId !== null) {
            $query->where('id', '>', (int)$maxId);
        }

        return $query->count();
    }

    public static function getPendingDedicationsCount(): int
    {
        return PetHealthUpdate::where('health_status', 'deceased')
            ->where(function ($q) {
                $q->whereNull('review_decision')->orWhereIn('review_decision', ['pending', 'investigation_pending']);
            })
            ->count();
    }

    public static function markDedicationsViewed(): void
    {
        $deceasedIds = PetHealthUpdate::where('health_status', 'deceased')->pluck('id');
        foreach ($deceasedIds as $dId) {
            self::markAsRead('dedication_' . $dId);
        }
    }
}