<?php

namespace App\Http\Controllers;

use App\Models\AdoptersProfile;
use App\Models\AdoptionApplication;
use App\Models\MedicalLog;
use App\Models\PetHealthUpdate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdopterProfileController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->query('search', ''));
        $filter = $request->query('filter', 'active');

        $query = AdoptionApplication::with([
                'pet.medicalLogs' => function($q) {
                    $q->latest('date');
                },
                'pet.healthUpdates' => function($q) {
                    $q->latest('check_in_date');
                }
            ])
            ->where('status', 'approved')
            ->whereNotNull('documents_verified_at');

        if (!empty($search)) {
            $matchedUserEmails = [];
            if (preg_match('/^(?:adp[-_\s]*)?(\d+)$/i', $search, $matches)) {
                $parsedUserId = (int)$matches[1];
                $matchedUserEmails = User::where('id', $parsedUserId)->pluck('email')->filter()->toArray();
            }

            $query->where(function($q) use ($search, $matchedUserEmails) {
                $q->where('applicant_name', 'like', "%{$search}%")
                  ->orWhere('applicant_email', 'like', "%{$search}%")
                  ->orWhere('applicant_phone', 'like', "%{$search}%")
                  ->orWhereHas('pet', function($petQ) use ($search) {
                      $petQ->where('name', 'like', "%{$search}%")
                           ->orWhere('breed', 'like', "%{$search}%")
                           ->orWhere('type', 'like', "%{$search}%");
                  });

                if (!empty($matchedUserEmails)) {
                    $q->orWhereIn('applicant_email', $matchedUserEmails);
                }
            });
        }

        $allApplications = $query->latest('updated_at')->get();

        // Preload users and adopters_profile by email
        $allEmails = $allApplications->pluck('applicant_email')->filter()->unique()->toArray();
        $userMap = User::whereIn('email', $allEmails)->get()->keyBy(function($u) {
            return strtolower(trim($u->email));
        });
        $profileMap = AdoptersProfile::whereIn('email', $allEmails)->get()->keyBy(function($p) {
            return strtolower(trim($p->email));
        });

        // Group by adopter email / name and compute compliance & status dynamically
        $allGroupedAdopters = $allApplications->groupBy(function($item) {
            return strtolower(trim($item->applicant_email ?: $item->applicant_name));
        })->map(function($apps) use ($userMap, $profileMap) {
            $primary = $apps->first();
            $emailKey = strtolower(trim($primary->applicant_email ?? ''));
            $user = $userMap->get($emailKey);
            $profile = $profileMap->get($emailKey);

            if (!$profile && !empty($emailKey)) {
                $profile = \App\Models\AdoptersProfile::firstOrCreate(
                    ['email' => $emailKey],
                    [
                        'user_id'      => $user?->id,
                        'adopter_code' => $user ? sprintf('ADP-%04d', $user->id) : sprintf('APP-%04d', $primary->id),
                        'full_name'    => $primary->applicant_name,
                        'phone'        => $primary->applicant_phone,
                        'status'       => 'active',
                    ]
                );
            }

            $adopterIdNumber = $profile?->adopter_code 
                ?: ($user ? sprintf('ADP-%04d', $user->id) : sprintf('APP-%04d', $primary->id));
            $avatarUrl = $user && !empty($user->avatar) ? $user->avatar_url : null;

            // Extract address from profile or application
            $resolvedAddress = $profile?->address;
            if (empty($resolvedAddress) && !empty($primary->message) && preg_match('/Address:\s*(.+?)(?=\n[A-Za-z\s]+:|$)/is', $primary->message, $m)) {
                $resolvedAddress = trim($m[1]);
            }

            // Calculate Monthly Mobile Report Compliance across all adopted pets
            $hasOverdueReport = false;
            $maxOverdueDays = 0;
            $hasNewAdoption = false;
            $minDueDays = 999;
            $nextEarliestDueDate = null;

            foreach ($apps as $app) {
                $pet = $app->pet;
                $adoptedDate = $app->approved_at ?? $app->signed_at ?? $app->updated_at ?? $app->created_at;
                $latestUpdate = $pet?->healthUpdates?->first();
                $isPetDeceased = ($pet?->status === 'deceased') || ($latestUpdate?->health_status === 'deceased');
                $app->is_pet_deceased = $isPetDeceased;

                if ($isPetDeceased) {
                    $app->is_report_overdue = false;
                    continue;
                }

                if ($latestUpdate) {
                    $lastReportDate = $latestUpdate->check_in_date ?? $latestUpdate->created_at;
                    $nextDue = Carbon::parse($lastReportDate)->addDays(30);
                    $app->next_report_due = $nextDue;

                    if (now()->greaterThan($nextDue)) {
                        $app->is_report_overdue = true;
                        $app->report_overdue_days = (int) $nextDue->diffInDays(now());
                        $hasOverdueReport = true;
                        if ($app->report_overdue_days > $maxOverdueDays) {
                            $maxOverdueDays = $app->report_overdue_days;
                        }
                    } else {
                        $app->is_report_overdue = false;
                        $app->report_due_days = (int) now()->diffInDays($nextDue);
                        if ($app->report_due_days < $minDueDays) {
                            $minDueDays = $app->report_due_days;
                            $nextEarliestDueDate = $nextDue;
                        }
                    }
                } else {
                    $nextDue = Carbon::parse($adoptedDate)->addDays(30);
                    $app->next_report_due = $nextDue;

                    if (now()->greaterThan($nextDue)) {
                        $app->is_report_overdue = true;
                        $app->report_overdue_days = (int) $nextDue->diffInDays(now());
                        $hasOverdueReport = true;
                        if ($app->report_overdue_days > $maxOverdueDays) {
                            $maxOverdueDays = $app->report_overdue_days;
                        }
                    } else {
                        $app->is_report_overdue = false;
                        $app->report_due_days = (int) now()->diffInDays($nextDue);
                        $hasNewAdoption = true;
                        if ($app->report_due_days < $minDueDays) {
                            $minDueDays = $app->report_due_days;
                            $nextEarliestDueDate = $nextDue;
                        }
                    }
                }
            }

            // Determine Adopter Status Code, Label, and Badge Theme
            $manualStatus = $profile?->status;
            if (in_array($manualStatus, ['restricted', 'blacklisted'])) {
                $statusCode = 'blacklisted';
                $statusLabel = 'Blacklisted';
                $badgeTheme = 'rose';
            } elseif ($hasOverdueReport) {
                $statusCode = 'inactive';
                $statusLabel = 'Reports Overdue';
                $badgeTheme = 'rose';
            } elseif ($hasNewAdoption) {
                $statusCode = 'active_new';
                $statusLabel = 'Active';
                $badgeTheme = 'emerald';
            } else {
                $statusCode = 'active';
                $statusLabel = 'Active';
                $badgeTheme = 'emerald';
            }

            return (object)[
                'profile_id'            => $profile?->id,
                'adopter_id_code'       => $adopterIdNumber,
                'status'                => $statusCode,
                'status_code'           => $statusCode,
                'status_label'          => $statusLabel,
                'badge_theme'           => $badgeTheme,
                'has_overdue_report'    => $hasOverdueReport,
                'max_overdue_days'      => $maxOverdueDays,
                'next_due_days'         => ($minDueDays === 999 ? null : $minDueDays),
                'next_due_date'         => $nextEarliestDueDate,
                'admin_notes'           => $profile?->admin_notes,
                'avatar'                => $avatarUrl,
                'applicant_name'        => $primary->applicant_name,
                'applicant_email'       => $primary->applicant_email,
                'applicant_phone'       => $profile?->phone ?: $primary->applicant_phone,
                'address'               => $resolvedAddress,
                'city'                  => $profile?->city,
                'province'              => $profile?->province,
                'latest_updated_at'     => $apps->max('updated_at'),
                'applications'          => $apps,
                'pets_count'            => $apps->count(),
            ];
        })->sortByDesc('latest_updated_at')->values();

        // Calculate summary counts across all adopters
        $totalApprovedAdopters = $allGroupedAdopters->count();
        $activeCount = $allGroupedAdopters->filter(fn($a) => in_array($a->status_code, ['active', 'active_new']))->count();
        $inactiveCount = $allGroupedAdopters->filter(fn($a) => $a->status_code === 'inactive')->count();
        $blacklistedCount = $allGroupedAdopters->filter(fn($a) => $a->status_code === 'blacklisted')->count();
        $restrictedCount = $blacklistedCount;
        $totalApprovedApplications = AdoptionApplication::where('status', 'approved')->count();

        $totalMonthlyReports = PetHealthUpdate::where('health_status', '!=', 'deceased')->count();
        $totalVetLogsCount = MedicalLog::where('is_external', true)->count();
        $pendingVetLogsCount = MedicalLog::where('is_external', true)->where('verification_status', 'pending')->count();
        $totalDedicationsCount = PetHealthUpdate::where('health_status', 'deceased')->count();
        $pendingDeceasedCount = PetHealthUpdate::where('health_status', 'deceased')->where(function($q) {
            $q->whereNull('review_decision')->orWhere('review_decision', 'pending');
        })->count();

        // Apply active filter (defaults to active compliant adopters)
        if ($filter === 'inactive' || $filter === 'overdue') {
            $filteredAdopters = $allGroupedAdopters->filter(fn($a) => $a->status_code === 'inactive');
        } elseif ($filter === 'blacklisted' || $filter === 'restricted') {
            $filteredAdopters = $allGroupedAdopters->filter(fn($a) => $a->status_code === 'blacklisted');
        } else {
            $filter = 'active';
            $filteredAdopters = $allGroupedAdopters->filter(fn($a) => in_array($a->status_code, ['active', 'active_new']));
        }

        $page = (int) $request->query('page', 1);
        $perPage = 10;
        $adopters = new LengthAwarePaginator(
            $filteredAdopters->forPage($page, $perPage)->values(),
            $filteredAdopters->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('adopters.index', compact(
            'adopters',
            'search',
            'filter',
            'totalApprovedAdopters',
            'totalApprovedApplications',
            'activeCount',
            'inactiveCount',
            'blacklistedCount',
            'restrictedCount',
            'totalMonthlyReports',
            'totalVetLogsCount',
            'pendingVetLogsCount',
            'totalDedicationsCount',
            'pendingDeceasedCount'
        ));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        if (Auth::user()?->role !== 'admin') {
            abort(403, 'Only an administrator can modify adopter standing and sanctions.');
        }

        $request->validate([
            'status'      => 'required|in:active,blacklisted,good_standing,restricted',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $profile = AdoptersProfile::findOrFail($id);
        $profile->update([
            'status'      => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        $statusDisplayName = match($request->status) {
            'blacklisted' => 'Blacklisted',
            default => 'Active'
        };

        return back()->with('success', "Updated status for {$profile->full_name} to {$statusDisplayName}.");
    }

    /**
     * Backward-compatibility wrapper for Health & Welfare Updates page.
     */
    public function monthlyReports(Request $request): View
    {
        return app(HealthWelfareUpdateController::class)->index($request);
    }

    /**
     * Backward-compatibility wrapper for Deceased Pet Report review.
     */
    public function reviewDeceasedReport(Request $request, int $id): RedirectResponse
    {
        return app(HealthWelfareUpdateController::class)->reviewDeceasedReport($request, $id);
    }
}
