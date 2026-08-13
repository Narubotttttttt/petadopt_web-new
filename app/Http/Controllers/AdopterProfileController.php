<?php

namespace App\Http\Controllers;

use App\Models\AdoptionApplication;
use App\Models\MedicalLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class AdopterProfileController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->query('search', ''));
        $filter = $request->query('filter', 'all');

        $query = AdoptionApplication::with(['pet.medicalLogs' => function($q) {
                $q->latest('date');
            }])
            ->where('status', 'approved');

        if (!empty($search)) {
            // Check if search looks like an Adopter ID (e.g., ADP-0001, ADP-1, ADP 1, or just an integer)
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

        if ($filter === 'overdue') {
            $query->whereHas('pet.medicalLogs', function($q) {
                $q->where('category', 'vaccination')
                  ->whereNotNull('next_due_date')
                  ->where('next_due_date', '<', now()->toDateString());
            });
        } elseif ($filter === 'due_soon') {
            $query->whereHas('pet.medicalLogs', function($q) {
                $q->where('category', 'vaccination')
                  ->whereNotNull('next_due_date')
                  ->whereBetween('next_due_date', [now()->toDateString(), now()->addDays(14)->toDateString()]);
            });
        } elseif ($filter === 'up_to_date') {
            $query->whereHas('pet.medicalLogs', function($q) {
                $q->where('category', 'vaccination')
                  ->whereNotNull('next_due_date')
                  ->where('next_due_date', '>', now()->addDays(14)->toDateString());
            });
        }

        $allApplications = $query->latest('updated_at')->get();

        // Preload users by email to quickly resolve user IDs
        $allEmails = $allApplications->pluck('applicant_email')->filter()->unique()->toArray();
        $userMap = User::whereIn('email', $allEmails)->get()->keyBy(function($u) {
            return strtolower(trim($u->email));
        });

        // Group by adopter email / name so multi-pet adopters are presented cleanly
        $groupedAdopters = $allApplications->groupBy(function($item) {
            return strtolower(trim($item->applicant_email ?: $item->applicant_name));
        })->map(function($apps) use ($userMap) {
            $primary = $apps->first();
            $emailKey = strtolower(trim($primary->applicant_email ?? ''));
            $user = $userMap->get($emailKey);
            $adopterIdNumber = $user ? sprintf('ADP-%04d', $user->id) : sprintf('APP-%04d', $primary->id);

            return (object)[
                'adopter_id_code'   => $adopterIdNumber,
                'applicant_name'    => $primary->applicant_name,
                'applicant_email'   => $primary->applicant_email,
                'applicant_phone'   => $primary->applicant_phone,
                'latest_updated_at' => $apps->max('updated_at'),
                'applications'      => $apps,
                'pets_count'        => $apps->count(),
            ];
        })->sortByDesc('latest_updated_at')->values();

        $page = (int) $request->query('page', 1);
        $perPage = 10;
        $adopters = new LengthAwarePaginator(
            $groupedAdopters->forPage($page, $perPage)->values(),
            $groupedAdopters->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Statistics
        $totalApprovedAdopters = $groupedAdopters->count();
        $totalApprovedApplications = AdoptionApplication::where('status', 'approved')->count();
        
        $overdueCount = AdoptionApplication::where('status', 'approved')
            ->whereHas('pet.medicalLogs', function($q) {
                $q->where('category', 'vaccination')
                  ->whereNotNull('next_due_date')
                  ->where('next_due_date', '<', now()->toDateString());
            })->count();

        $dueSoonCount = AdoptionApplication::where('status', 'approved')
            ->whereHas('pet.medicalLogs', function($q) {
                $q->where('category', 'vaccination')
                  ->whereNotNull('next_due_date')
                  ->whereBetween('next_due_date', [now()->toDateString(), now()->addDays(14)->toDateString()]);
            })->count();

        return view('adopters.index', compact(
            'adopters',
            'search',
            'filter',
            'totalApprovedAdopters',
            'totalApprovedApplications',
            'overdueCount',
            'dueSoonCount'
        ));
    }
}
