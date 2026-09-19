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
            $latestTimestamp = 0;
            
            foreach ($apps as $app) {
                $pet = $app->pet;
                if (!$pet) continue;
                
                $petName = $pet->name ?: 'Pet no. ' . $pet->id;
                $latestUpdate = $pet->healthUpdates->first();
                
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
            
            if (!empty($petDetails) && ($hasRecentCheckin || $hasOverdue)) {
                $id = 'adopter_' . md5($emailKey . '_' . $latestTimestamp);
                $category = $hasOverdue ? 'overdue' : 'checkin';
                $severity = $hasOverdue ? 'danger' : 'success';
                $title = $hasOverdue ? '30-Day Check-in Overdue' : 'Monthly Health Check-in';
                
                $adopterNameParts = preg_split('/\s+/', trim($adopterName));
                $adopterInitials = count($adopterNameParts) >= 2 
                    ? strtoupper(mb_substr($adopterNameParts[0], 0, 1) . mb_substr(end($adopterNameParts), 0, 1))
                    : strtoupper(mb_substr($adopterName, 0, 1));

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
                    'pets_count' => count($apps),
                    'pet_details' => $petDetails,
                    'time' => $latestTimestamp ? Carbon::createFromTimestamp($latestTimestamp)->diffForHumans() : 'Recently',
                    'is_read' => false,
                    'url' => url('/adopters') . '?search=' . urlencode($adopterName),
                    'action_url' => url('/adopters') . '?search=' . urlencode($adopterName),
                    'action_hint' => 'View adopter profile & check-in history',
                    'created_at' => $latestTimestamp ?: now()->timestamp,
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
}