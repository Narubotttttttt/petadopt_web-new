<?php

namespace App\Http\Controllers;

use App\Models\AdoptionApplication;
use App\Models\MedicalLog;
use App\Models\Pet;
use App\Models\PetHealthUpdate;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PetHistoryController extends Controller
{
    /**
     * Display the comprehensive history and lifecycle dossier of all pets.
     */
    public function index(Request $request)
    {
        $selectedYear = $request->filled('year') && $request->query('year') !== 'all' ? (int) $request->query('year') : null;
        $eventType    = $request->query('event_type', 'all'); // 'all', 'intake', 'medical', 'adoption', 'health_update'
        $status       = $request->query('status', 'all');     // 'all', 'in_shelter', 'available', 'pending', 'adopted'
        $species      = $request->query('species', 'all');    // 'all', 'dog', 'cat'
        $search       = trim((string) $request->query('q', ''));
        $petIdFilter  = $request->query('pet_id');

        $isSqlite = DB::getDriverName() === 'sqlite';
        $yearIntake = $isSqlite ? "CAST(strftime('%Y', created_at) AS INTEGER)" : 'YEAR(created_at)';

        // 1. Available years for intake filtering
        $intakeYears = Pet::selectRaw("DISTINCT {$yearIntake} as yr")->pluck('yr')->filter()->map(fn($y) => (int)$y)->all();
        $availableYears = array_values(array_unique(array_merge([now()->year], $intakeYears)));
        rsort($availableYears);

        // 2. High-Level Summary Statistics (Historical)
        $totalHistoricalPets = Pet::count();
        $totalHistoricalAdoptions = Pet::where('status', 'adopted')->count();
        $totalHistoricalMedicalLogs = MedicalLog::count();
        $totalActiveInShelter = Pet::whereIn('status', ['available', 'pending'])->count();
        $totalDogsCount = Pet::where('type', 'dog')->count();
        $totalCatsCount = Pet::where('type', 'cat')->count();

        // 3. Query Pets with complete relations
        $petsQuery = Pet::with([
            'addedBy',
            'medicalLogs.creator',
            'adoptionApplications' => function ($q) {
                $q->with(['user.adoptersProfile', 'staff'])->latest('created_at');
            },
            'healthUpdates' => function ($q) {
                $q->with('user')->latest('created_at');
            },
        ]);

        // Filter by Species
        if ($species !== 'all') {
            $petsQuery->where('type', $species);
        }

        // Filter by Status / Event Type
        if ($status !== 'all') {
            if ($status === 'in_shelter') {
                $petsQuery->whereIn('status', ['available', 'pending']);
            } else {
                $petsQuery->where('status', $status);
            }
        } elseif ($eventType !== 'all') {
            if ($eventType === 'adoption') {
                $petsQuery->where(function ($q) {
                    $q->where('status', 'adopted')
                      ->orWhereHas('adoptionApplications');
                });
            } elseif ($eventType === 'medical') {
                $petsQuery->whereHas('medicalLogs');
            } elseif ($eventType === 'health_update') {
                $petsQuery->whereHas('healthUpdates');
            }
        }

        // Filter by Intake Year
        if ($selectedYear) {
            $petsQuery->whereYear('created_at', $selectedYear);
        }

        // Filter by specific Pet ID
        if ($petIdFilter) {
            $petsQuery->where('id', $petIdFilter);
        }

        // Search filter (Pet Name, Breed, Color, ID, or Adopter Name/Email)
        if (!empty($search)) {
            $petsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('breed', 'like', "%{$search}%")
                  ->orWhere('color', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('adoptionApplications', function ($aq) use ($search) {
                      $aq->where('applicant_name', 'like', "%{$search}%")
                         ->orWhere('applicant_email', 'like', "%{$search}%");
                  });
            });
        }

        // Paginate pets
        $paginator = $petsQuery->latest('created_at')->paginate(15)->withQueryString();
        $paginator->fragment('history-records');

        // Transform pets into rich historical dossier records
        $paginator->through(function ($p) {
            $medicalBadgeClasses = [
                'vaccination'    => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                'deworming'      => 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800/60',
                'treatment'      => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                'checkup'        => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                'surgery'        => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60',
                'injury_illness' => 'bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-800/60',
            ];

            // Build chronological timeline for this pet
            $timeline = new Collection();

            // 1. Shelter Intake Milestone
            $timeline->push([
                'id'          => 'intake-' . $p->id,
                'event_type'  => 'intake',
                'badge_label' => 'Intake / Rescued',
                'badge_class' => 'bg-[#199CA4]/10 text-[#199CA4] dark:bg-teal-950/40 dark:text-[#41C1CB] border-[#199CA4]/25',
                'title'       => 'Admitted to Shelter Catalog',
                'date'        => $p->created_at ? $p->created_at->format('M d, Y') : 'Unknown',
                'timestamp'   => $p->created_at ?? now(),
                'meta'        => 'Registered by ' . ($p->added_by_name ?: ($p->addedBy?->name ?: 'Staff')),
                'description' => $p->description ?: "Pet #{$p->id} (" . ucfirst($p->type ?? 'pet') . ($p->breed ? ' • ' . $p->breed : '') . ") was registered into shelter care.",
                'details'     => [
                    'added_by'    => $p->added_by_name ?: ($p->addedBy?->name ?: 'Staff'),
                    'intake_date' => $p->created_at ? $p->created_at->format('M d, Y h:i A') : 'Unknown',
                ],
            ]);

            // 2. Clinical Medical Logs
            foreach ($p->medicalLogs as $mLog) {
                $catName = ucfirst(str_replace('_', ' ', $mLog->category));
                $timeline->push([
                    'id'          => 'med-' . $mLog->id,
                    'event_type'  => 'medical',
                    'badge_label' => $catName,
                    'badge_class' => $medicalBadgeClasses[$mLog->category] ?? 'bg-slate-100 text-slate-700 border-slate-200',
                    'title'       => 'Clinical ' . $catName . ' Performed',
                    'date'        => $mLog->date ? $mLog->date->format('M d, Y') : 'Unknown',
                    'timestamp'   => $mLog->date ? Carbon::parse($mLog->date)->startOfDay() : ($mLog->created_at ?? now()),
                    'meta'        => 'Administered by ' . ($mLog->administered_by ?: 'Veterinary Staff'),
                    'description' => $mLog->description ?: ('Administered by ' . ($mLog->administered_by ?: 'Veterinary Staff') . ($mLog->next_due_date ? ' • Next due: ' . $mLog->next_due_date->format('M d, Y') : '')),
                    'details'     => [
                        'administered_by' => $mLog->administered_by ?: 'Veterinary Staff',
                        'recorded_by'     => $mLog->creator?->name ?: 'Staff',
                        'next_due_date'   => $mLog->next_due_date ? $mLog->next_due_date->format('M d, Y') : null,
                        'is_overdue'      => $mLog->next_due_date ? $mLog->next_due_date->isPast() : false,
                        'notes'           => $mLog->description ?: 'No additional clinical remarks.',
                        'edit_url'        => route('medical-logs.edit', $mLog),
                    ],
                ]);
            }

            // 3. Adoption Applications & Finalizations
            $approvedApp = $p->adoptionApplications->firstWhere('status', 'approved');
            foreach ($p->adoptionApplications as $app) {
                $isApproved = $app->status === 'approved';
                $isRejected = $app->status === 'rejected';
                $eventDate = $app->approved_at ?: $app->created_at;
                $statusClasses = [
                    'approved'     => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                    'rejected'     => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60',
                    'pending'      => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                    'under_review' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                ];

                $timeline->push([
                    'id'          => 'adopt-' . $app->id,
                    'event_type'  => 'adoption',
                    'badge_label' => 'Adoption: ' . ucfirst($app->status),
                    'badge_class' => $statusClasses[$app->status] ?? 'bg-slate-100 text-slate-700 border-slate-200',
                    'title'       => $isApproved ? 'Adoption Finalized' : ($isRejected ? 'Adoption Application Rejected' : 'Adoption Application Submitted'),
                    'date'        => $eventDate ? Carbon::parse($eventDate)->format('M d, Y') : 'Unknown',
                    'timestamp'   => $eventDate ? Carbon::parse($eventDate) : now(),
                    'meta'        => 'Applicant: ' . ($app->applicant_name ?: 'Applicant') . ' • App #' . $app->id,
                    'description' => 'Adopter: ' . ($app->applicant_name ?: 'Applicant') . ' • Email: ' . ($app->applicant_email ?: '—'),
                    'details'     => [
                        'applicant_name'  => $app->applicant_name,
                        'applicant_email' => $app->applicant_email,
                        'applicant_phone' => $app->applicant_phone,
                        'status'          => $app->status,
                        'approved_at'     => $app->approved_at ? Carbon::parse($app->approved_at)->format('M d, Y h:i A') : null,
                        'staff_name'      => $app->staff_name ?: ($app->staff?->name ?: 'Staff'),
                        'evaluator_name'  => $app->evaluator_name ?: ($app->staff_name ?: 'Shelter Staff'),
                        'contract_url'    => $app->contract_pdf_path ? route('adoption-applications.contract', $app->id) : null,
                        'action_url'      => route('adoption-applications.show', $app),
                        'notes'           => $app->evaluation_notes ?: ($app->message ?: 'No additional notes.'),
                    ],
                ]);
            }

            // 4. Post-Adoption Health Updates
            foreach ($p->healthUpdates as $hu) {
                $timeline->push([
                    'id'          => 'hu-' . $hu->id,
                    'event_type'  => 'health_update',
                    'badge_label' => 'Health Update',
                    'badge_class' => 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800/60',
                    'title'       => 'Post-Adoption Wellness Update: ' . ucfirst($hu->health_status ?: 'Good Condition'),
                    'date'        => $hu->created_at ? $hu->created_at->format('M d, Y') : 'Unknown',
                    'timestamp'   => $hu->created_at ?? now(),
                    'meta'        => 'Submitted by ' . ($hu->user?->name ?: 'Adopter'),
                    'description' => $hu->notes ?: 'Adopter submitted post-adoption pet condition update.',
                    'details'     => [
                        'health_status' => $hu->health_status ?: 'Good Condition',
                        'notes'         => $hu->notes ?: 'No remarks.',
                        'adopter_name'  => $hu->user?->name ?: 'Adopter',
                        'photo_url'     => $hu->photo_url,
                    ],
                ]);
            }

            // Sorted timeline: newest first
            $sortedTimeline = $timeline->sortByDesc('timestamp')->values()->all();

            $latestMed = $p->medicalLogs->first();

            return [
                'id'                  => $p->id,
                'pet_id'              => $p->id,
                'name'                => $p->name ?: 'Pet #' . $p->id,
                'pet_name'            => $p->name ?: 'Pet #' . $p->id,
                'type'                => $p->type,
                'pet_type'            => $p->type,
                'breed'               => $p->breed ?: 'Mixed Breed',
                'pet_breed'           => $p->breed ?: 'Mixed Breed',
                'color'               => $p->color ?: '—',
                'pet_color'           => $p->color ?: '—',
                'gender'              => $p->gender ?: 'Unknown',
                'pet_gender'          => $p->gender ?: 'Unknown',
                'age'                 => $p->age ?: 'Unknown',
                'pet_age'             => $p->age ?: 'Unknown',
                'status'              => $p->status,
                'pet_status'          => $p->status,
                'photo_url'           => $p->photo_url,
                'pet_photo'           => $p->photo_url,
                'description'         => $p->description ?: 'Registered into shelter care.',
                'pet_description'     => $p->description ?: 'Registered into shelter care.',
                'intake_date'         => $p->created_at ? $p->created_at->format('M d, Y') : 'Unknown',
                'intake_datetime'     => $p->created_at ? $p->created_at->format('M d, Y h:i A') : 'Unknown',
                'intake_diff'         => $p->created_at ? $p->created_at->diffForHumans() : '—',
                'added_by'            => $p->added_by_name ?: ($p->addedBy?->name ?: 'Staff'),
                'medical_logs_count'  => $p->medicalLogs->count(),
                'latest_medical'      => $latestMed ? [
                    'category'        => ucfirst(str_replace('_', ' ', $latestMed->category)),
                    'date'            => $latestMed->date ? $latestMed->date->format('M d, Y') : 'Unknown',
                    'administered_by' => $latestMed->administered_by ?: ($latestMed->creator?->name ?: 'Staff'),
                ] : null,
                'adoption'            => $approvedApp ? [
                    'applicant_name'  => $approvedApp->applicant_name,
                    'applicant_email' => $approvedApp->applicant_email,
                    'applicant_phone' => $approvedApp->applicant_phone,
                    'approved_at'     => $approvedApp->approved_at ? Carbon::parse($approvedApp->approved_at)->format('M d, Y') : null,
                    'contract_url'    => $approvedApp->contract_pdf_path ? route('adoption-applications.contract', $approvedApp->id) : null,
                ] : null,
                'timeline'            => $sortedTimeline,
                'total_events'        => count($sortedTimeline),
                'action_url'          => route('pets.show', $p),
                'action_label'        => 'View History',
            ];
        });

        return view('pet-history.index', [
            'events'                     => $paginator,
            'selectedYear'               => $selectedYear,
            'availableYears'             => $availableYears,
            'eventType'                  => $eventType,
            'status'                     => $status,
            'species'                    => $species,
            'search'                     => $search,
            'petIdFilter'                => $petIdFilter,
            'totalHistoricalPets'        => $totalHistoricalPets,
            'totalHistoricalAdoptions'   => $totalHistoricalAdoptions,
            'totalHistoricalMedicalLogs' => $totalHistoricalMedicalLogs,
            'totalActiveInShelter'       => $totalActiveInShelter,
            'totalDogsCount'             => $totalDogsCount,
            'totalCatsCount'             => $totalCatsCount,
        ]);
    }
}
