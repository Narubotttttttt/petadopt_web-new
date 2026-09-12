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
     * Display a comprehensive pet history log, KPIs, and analytics.
     */
    public function index(Request $request)
    {
        $selectedYear = (int) $request->query('year', now()->year);
        $eventType    = $request->query('event_type', 'all'); // all, intake, medical, adoption, health_update
        $species      = $request->query('species', 'all');       // all, dog, cat
        $search       = trim((string) $request->query('q', ''));
        $petIdFilter  = $request->query('pet_id');

        $isSqlite = DB::getDriverName() === 'sqlite';
        $monthAdoption = $isSqlite ? "CAST(strftime('%m', COALESCE(approved_at, created_at)) AS INTEGER)" : 'MONTH(COALESCE(approved_at, created_at))';
        $yearAdoption  = $isSqlite ? "CAST(strftime('%Y', COALESCE(approved_at, created_at)) AS INTEGER)" : 'YEAR(COALESCE(approved_at, created_at))';
        $monthIntake   = $isSqlite ? "CAST(strftime('%m', created_at) AS INTEGER)" : 'MONTH(created_at)';
        $yearIntake    = $isSqlite ? "CAST(strftime('%Y', created_at) AS INTEGER)" : 'YEAR(created_at)';
        $monthMedical  = $isSqlite ? "CAST(strftime('%m', date) AS INTEGER)" : 'MONTH(date)';
        $yearMedical   = $isSqlite ? "CAST(strftime('%Y', date) AS INTEGER)" : 'YEAR(date)';

        // 1. Available years for filtering
        $intakeYears = Pet::selectRaw("DISTINCT {$yearIntake} as yr")->pluck('yr')->filter()->all();
        $adoptionYears = AdoptionApplication::whereNotNull('created_at')->selectRaw("DISTINCT {$yearAdoption} as yr")->pluck('yr')->filter()->all();
        $medicalYears = MedicalLog::selectRaw("DISTINCT {$yearMedical} as yr")->pluck('yr')->filter()->all();

        $availableYears = array_values(array_unique(array_merge([now()->year], $intakeYears, $adoptionYears, $medicalYears)));
        rsort($availableYears);

        // 2. High-Level Summary Statistics (Historical)
        $totalHistoricalPets = Pet::count();
        $totalHistoricalAdoptions = AdoptionApplication::where('status', 'approved')->count();
        $totalHistoricalMedicalLogs = MedicalLog::count();
        $totalActiveInShelter = Pet::whereIn('status', ['available', 'pending'])->count();
        $totalDogsCount = Pet::where('type', 'dog')->count();
        $totalCatsCount = Pet::where('type', 'cat')->count();

        // 3. Year-Specific Trends for Chart.js
        $intakesByMonth = Pet::selectRaw("{$monthIntake} as month, COUNT(*) as total")
            ->whereRaw("{$yearIntake} = ?", [$selectedYear])
            ->groupBy('month')
            ->pluck('total', 'month');

        $adoptionsByMonth = AdoptionApplication::selectRaw("{$monthAdoption} as month, COUNT(*) as total")
            ->where('status', 'approved')
            ->whereRaw("{$yearAdoption} = ?", [$selectedYear])
            ->groupBy('month')
            ->pluck('total', 'month');

        $medicalsByMonth = MedicalLog::selectRaw("{$monthMedical} as month, COUNT(*) as total")
            ->whereRaw("{$yearMedical} = ?", [$selectedYear])
            ->groupBy('month')
            ->pluck('total', 'month');

        $chartMonths = [];
        $chartIntakes = [];
        $chartAdoptions = [];
        $chartMedicals = [];

        foreach (range(1, 12) as $m) {
            $chartMonths[] = Carbon::create()->month($m)->format('M');
            $chartIntakes[] = (int) ($intakesByMonth->get($m, 0));
            $chartAdoptions[] = (int) ($adoptionsByMonth->get($m, 0));
            $chartMedicals[] = (int) ($medicalsByMonth->get($m, 0));
        }

        // Medical procedure breakdown
        $medicalCategoryBreakdown = MedicalLog::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        // 4. Build Unified Chronological Event Timeline
        $timelineEvents = new Collection();

        // (A) Pet Intake Events
        if (in_array($eventType, ['all', 'intake'])) {
            $petsQuery = Pet::query();
            if ($species !== 'all') {
                $petsQuery->where('type', $species);
            }
            if ($petIdFilter) {
                $petsQuery->where('id', $petIdFilter);
            }
            if (!empty($search)) {
                $petsQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('breed', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%");
                });
            }
            if ($selectedYear) {
                $petsQuery->whereYear('created_at', $selectedYear);
            }

            $pets = $petsQuery->get();
            foreach ($pets as $p) {
                $timelineEvents->push([
                    'id'                  => 'intake-' . $p->id,
                    'raw_id'              => $p->id,
                    'timestamp'           => $p->created_at ?? now(),
                    'date'                => $p->created_at ? $p->created_at->format('M d, Y') : 'Unknown',
                    'event_type'          => 'intake',
                    'badge_label'         => 'Intake / Rescued',
                    'badge_class'         => 'bg-[#199CA4]/10 text-[#199CA4] dark:bg-teal-950/40 dark:text-[#41C1CB] border-[#199CA4]/25',
                    'pet_id'              => $p->id,
                    'pet_name'            => $p->name ?: 'Pet #' . $p->id,
                    'pet_type'            => $p->type,
                    'pet_breed'           => $p->breed,
                    'pet_photo'           => $p->photo_url,
                    'pet_gender'          => $p->gender,
                    'pet_age'             => $p->age,
                    'pet_size'            => $p->size,
                    'pet_weight'          => $p->weight,
                    'pet_color'           => $p->color,
                    'pet_status'          => $p->status,
                    'pet_health_status'   => $p->health_status ?: 'Good Condition',
                    'pet_spayed_neutered' => $p->spayed_neutered ? 'Yes' : 'No',
                    'pet_vaccinated'      => $p->vaccinated ? 'Yes' : 'No',
                    'title'               => 'Admitted to Shelter Catalog',
                    'description'         => "Pet #{$p->id} (" . ucfirst($p->type ?? 'pet') . ($p->breed ? ' • ' . $p->breed : '') . ") was registered into shelter care.",
                    'meta'                => 'Registered by ' . ($p->added_by_name ?: ($p->addedBy?->name ?: 'Staff')),
                    'recorded_by'         => $p->added_by_name ?: ($p->addedBy?->name ?: 'Staff'),
                    'intake_date'         => $p->created_at ? $p->created_at->format('M d, Y h:i A') : 'Unknown',
                    'pet_description'     => $p->description ?: 'Registered into shelter care.',
                    'notes'               => $p->description ?: 'Registered into shelter care.',
                    'action_url'          => route('pets.show', $p),
                    'pet_url'             => route('pets.show', $p),
                    'action_label'        => 'View Pet',
                ]);
            }
        }

        // (B) Medical & Clinical Events
        if (in_array($eventType, ['all', 'medical'])) {
            $medQuery = MedicalLog::with(['pet', 'creator']);
            if ($species !== 'all') {
                $medQuery->whereHas('pet', function ($q) use ($species) {
                    $q->where('type', $species);
                });
            }
            if ($petIdFilter) {
                $medQuery->where('pet_id', $petIdFilter);
            }
            if (!empty($search)) {
                $medQuery->where(function ($q) use ($search) {
                    $q->where('administered_by', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%")
                      ->orWhereHas('pet', function ($pq) use ($search) {
                          $pq->where('name', 'like', "%{$search}%")
                             ->orWhere('breed', 'like', "%{$search}%")
                             ->orWhere('id', 'like', "%{$search}%");
                      });
                });
            }
            if ($selectedYear) {
                $medQuery->whereYear('date', $selectedYear);
            }

            $medLogs = $medQuery->get();
            foreach ($medLogs as $mLog) {
                $categoryName = ucfirst(str_replace('_', ' ', $mLog->category));
                $badgeClasses = [
                    'vaccination'    => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                    'deworming'      => 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800/60',
                    'treatment'      => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                    'checkup'        => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                    'surgery'        => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60',
                    'injury_illness' => 'bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-800/60',
                ];

                $p = $mLog->pet;
                $timelineEvents->push([
                    'id'                  => 'med-' . $mLog->id,
                    'raw_id'              => $mLog->id,
                    'timestamp'           => $mLog->date ? Carbon::parse($mLog->date)->startOfDay() : ($mLog->created_at ?? now()),
                    'date'                => $mLog->date ? $mLog->date->format('M d, Y') : 'Unknown',
                    'event_type'          => 'medical',
                    'badge_label'         => $categoryName,
                    'badge_class'         => $badgeClasses[$mLog->category] ?? 'bg-slate-100 text-slate-700 border-slate-200',
                    'pet_id'              => $p?->id,
                    'pet_name'            => $p?->name ?: ($p ? 'Pet #' . $p->id : 'Unknown Pet'),
                    'pet_type'            => $p?->type,
                    'pet_breed'           => $p?->breed,
                    'pet_photo'           => $p?->photo_url,
                    'pet_gender'          => $p?->gender,
                    'pet_age'             => $p?->age,
                    'pet_size'            => $p?->size,
                    'pet_weight'          => $p?->weight,
                    'pet_color'           => $p?->color,
                    'pet_status'          => $p?->status,
                    'pet_health_status'   => $p?->health_status ?: 'Good Condition',
                    'pet_spayed_neutered' => $p?->spayed_neutered ? 'Yes' : 'No',
                    'pet_vaccinated'      => $p?->vaccinated ? 'Yes' : 'No',
                    'title'               => 'Clinical ' . $categoryName . ' Performed',
                    'description'         => $mLog->description ?: ('Administered by ' . ($mLog->administered_by ?: 'Veterinary Staff') . ($mLog->next_due_date ? ' • Next due: ' . $mLog->next_due_date->format('M d, Y') : '')),
                    'meta'                => 'Logged by ' . ($mLog->creator?->name ?: 'Staff'),
                    'category'            => $mLog->category,
                    'category_name'       => $categoryName,
                    'administered_by'     => $mLog->administered_by ?: 'Veterinary Staff',
                    'recorded_by'         => $mLog->creator?->name ?: 'Staff',
                    'next_due_date'       => $mLog->next_due_date ? $mLog->next_due_date->format('M d, Y') : null,
                    'is_overdue'          => $mLog->next_due_date ? $mLog->next_due_date->isPast() : false,
                    'notes'               => $mLog->description ?: 'No additional clinical remarks recorded.',
                    'intake_date'         => $p?->created_at ? $p->created_at->format('M d, Y h:i A') : 'Unknown',
                    'pet_description'     => $p?->description ?: 'No additional pet background notes.',
                    'action_url'          => $p ? route('pets.show', $p) : route('medical-logs.index'),
                    'edit_url'            => route('medical-logs.edit', $mLog),
                    'pet_url'             => $p ? route('pets.show', $p) : null,
                    'action_label'        => 'View Details',
                ]);
            }
        }

        // (C) Adoption Events
        if (in_array($eventType, ['all', 'adoption'])) {
            $adoptionsQuery = AdoptionApplication::with(['pet', 'user.adoptersProfile', 'staff']);
            if ($species !== 'all') {
                $adoptionsQuery->whereHas('pet', function ($q) use ($species) {
                    $q->where('type', $species);
                });
            }
            if ($petIdFilter) {
                $adoptionsQuery->where('pet_id', $petIdFilter);
            }
            if (!empty($search)) {
                $adoptionsQuery->where(function ($q) use ($search) {
                    $q->where('applicant_name', 'like', "%{$search}%")
                      ->orWhere('applicant_email', 'like', "%{$search}%")
                      ->orWhereHas('pet', function ($pq) use ($search) {
                          $pq->where('name', 'like', "%{$search}%")
                             ->orWhere('breed', 'like', "%{$search}%")
                             ->orWhere('id', 'like', "%{$search}%");
                      });
                });
            }
            if ($selectedYear) {
                $adoptionsQuery->where(function ($q) use ($selectedYear) {
                    $q->whereYear('approved_at', $selectedYear)
                      ->orWhere(function ($sq) use ($selectedYear) {
                          $sq->whereNull('approved_at')->whereYear('created_at', $selectedYear);
                      });
                });
            }

            $adoptions = $adoptionsQuery->get();
            foreach ($adoptions as $app) {
                $isApproved = $app->status === 'approved';
                $isRejected = $app->status === 'rejected';
                $eventDate = $app->approved_at ?: $app->created_at;
                $p = $app->pet;

                $statusClasses = [
                    'approved'     => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                    'rejected'     => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60',
                    'pending'      => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                    'under_review' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                ];

                $title = $isApproved ? 'Adoption Finalized' : ($isRejected ? 'Adoption Rejected' : 'Adoption Application Filed');

                $timelineEvents->push([
                    'id'                       => 'adopt-' . $app->id,
                    'raw_id'                   => $app->id,
                    'timestamp'                => $eventDate ? Carbon::parse($eventDate) : now(),
                    'date'                     => $eventDate ? Carbon::parse($eventDate)->format('M d, Y') : 'Unknown',
                    'application_date'         => $app->created_at ? $app->created_at->format('M d, Y h:i A') : 'Unknown',
                    'approved_at_formatted'    => $app->approved_at ? Carbon::parse($app->approved_at)->format('M d, Y h:i A') : null,
                    'event_type'               => 'adoption',
                    'badge_label'              => 'Adoption: ' . ucfirst($app->status),
                    'badge_class'              => $statusClasses[$app->status] ?? 'bg-slate-100 text-slate-700 border-slate-200',
                    'pet_id'                   => $p?->id,
                    'pet_name'                 => $p?->name ?: ($p ? 'Pet #' . $p->id : 'Unknown Pet'),
                    'pet_type'                 => $p?->type,
                    'pet_breed'                => $p?->breed,
                    'pet_photo'                => $p?->photo_url,
                    'pet_gender'               => $p?->gender,
                    'pet_age'                  => $p?->age,
                    'pet_size'                 => $p?->size,
                    'pet_weight'               => $p?->weight,
                    'pet_color'                => $p?->color,
                    'pet_status'               => $p?->status,
                    'pet_health_status'        => $p?->health_status ?: 'Good Condition',
                    'pet_spayed_neutered'      => $p?->spayed_neutered ? 'Yes' : 'No',
                    'pet_vaccinated'           => $p?->vaccinated ? 'Yes' : 'No',
                    'title'                    => $title,
                    'description'              => 'Adopter: ' . ($app->applicant_name ?: 'Applicant') . ' • Email: ' . ($app->applicant_email ?: '—'),
                    'meta'                     => 'Application #' . $app->id,
                    'applicant_name'           => $app->applicant_name,
                    'applicant_email'          => $app->applicant_email,
                    'applicant_phone'          => $app->applicant_phone,
                    'id_type'                  => $app->id_type,
                    'valid_id_url'             => $app->valid_id_url,
                    'barangay_certificate_url' => $app->barangay_certificate_url,
                    'signature_url'            => $app->signature_url,
                    'signed_at'                => $app->signed_at ? $app->signed_at->format('M d, Y h:i A') : null,
                    'staff_signature_url'      => $app->staff_signature_url,
                    'staff_signed_at'          => $app->staff_signed_at ? $app->staff_signed_at->format('M d, Y h:i A') : null,
                    'staff_name'               => $app->staff_name ?: ($app->staff?->name ?: 'Staff'),
                    'status'                   => $app->status,
                    'scheduled_at'             => $app->scheduled_at ? $app->scheduled_at->format('M d, Y h:i A') : null,
                    'evaluation_notes'         => $app->evaluation_notes ?: ($app->message ?: 'No additional notes.'),
                    'evaluation_recommendation'=> $app->evaluation_recommendation,
                    'evaluator_name'           => $app->evaluator_name ?: ($app->staff_name ?: 'Shelter Staff'),
                    'message'                  => $app->message,
                    'contract_url'             => $app->contract_pdf_path ? route('adoption-applications.contract', $app->id) : null,
                    'intake_date'              => $p?->created_at ? $p->created_at->format('M d, Y h:i A') : 'Unknown',
                    'pet_description'          => $p?->description ?: 'No additional pet background notes.',
                    'action_url'               => route('adoption-applications.show', $app),
                    'app_url'                  => route('adoption-applications.show', $app),
                    'pet_url'                  => $p ? route('pets.show', $p) : null,
                    'action_label'             => 'View Application',
                ]);
            }
        }

        // (D) Pet Health Updates (Adopter Post-Adoption Check-ins)
        if (in_array($eventType, ['all', 'health_update'])) {
            $updatesQuery = PetHealthUpdate::with(['pet', 'user']);
            if ($species !== 'all') {
                $updatesQuery->whereHas('pet', function ($q) use ($species) {
                    $q->where('type', $species);
                });
            }
            if ($petIdFilter) {
                $updatesQuery->where('pet_id', $petIdFilter);
            }
            if (!empty($search)) {
                $updatesQuery->where(function ($q) use ($search) {
                    $q->where('health_status', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('pet', function ($pq) use ($search) {
                          $pq->where('name', 'like', "%{$search}%")
                             ->orWhere('breed', 'like', "%{$search}%");
                      });
                });
            }
            if ($selectedYear) {
                $updatesQuery->whereYear('check_in_date', $selectedYear);
            }

            $healthUpdates = $updatesQuery->get();
            foreach ($healthUpdates as $hu) {
                $p = $hu->pet;
                $timelineEvents->push([
                    'id'                  => 'hu-' . $hu->id,
                    'raw_id'              => $hu->id,
                    'timestamp'           => $hu->check_in_date ? Carbon::parse($hu->check_in_date)->startOfDay() : ($hu->created_at ?? now()),
                    'date'                => $hu->check_in_date ? Carbon::parse($hu->check_in_date)->format('M d, Y') : 'Unknown',
                    'event_type'          => 'health_update',
                    'badge_label'         => 'Post-Adoption Check-in',
                    'badge_class'         => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                    'pet_id'              => $p?->id,
                    'pet_name'            => $p?->name ?: ($p ? 'Pet #' . $p->id : 'Unknown Pet'),
                    'pet_type'            => $p?->type,
                    'pet_breed'           => $p?->breed,
                    'pet_photo'           => $p?->photo_url,
                    'pet_gender'          => $p?->gender,
                    'pet_age'             => $p?->age,
                    'pet_size'            => $p?->size,
                    'pet_weight'          => $p?->weight,
                    'pet_color'           => $p?->color,
                    'pet_status'          => $p?->status,
                    'pet_health_status'   => $p?->health_status ?: 'Good Condition',
                    'pet_spayed_neutered' => $p?->spayed_neutered ? 'Yes' : 'No',
                    'pet_vaccinated'      => $p?->vaccinated ? 'Yes' : 'No',
                    'title'               => 'Health Update: ' . ucfirst($hu->health_status ?? 'Healthy'),
                    'description'         => ($hu->notes ? mb_strimwidth($hu->notes, 0, 100, '...') : 'Weight: ' . ($hu->weight ? $hu->weight . ' kg' : 'N/A')),
                    'meta'                => 'Submitted by ' . ($hu->user?->name ?: 'Adopter'),
                    'health_status'       => ucfirst($hu->health_status ?? 'Healthy'),
                    'weight'              => $hu->weight ? $hu->weight . ' kg' : null,
                    'notes'               => $hu->notes ?: 'No notes submitted for this check-in.',
                    'submitted_by'        => $hu->user?->name ?: 'Adopter',
                    'photo_url'           => $hu->photo_url,
                    'staff_remarks'       => $hu->staff_remarks,
                    'intake_date'         => $p?->created_at ? $p->created_at->format('M d, Y h:i A') : 'Unknown',
                    'pet_description'     => $p?->description ?: 'No additional pet background notes.',
                    'action_url'          => $p ? route('pets.show', $p) : route('adopters.index'),
                    'pet_url'             => $p ? route('pets.show', $p) : null,
                    'action_label'        => 'View Pet',
                ]);
            }
        }

        // Sort timeline descending by timestamp
        $sortedEvents = $timelineEvents->sortByDesc('timestamp')->values();

        // Paginate in-memory collection
        $perPage = 15;
        $currentPage = (int) $request->query('page', 1);
        $paginatedEvents = new LengthAwarePaginator(
            $sortedEvents->forPage($currentPage, $perPage)->values(),
            $sortedEvents->count(),
            $perPage,
            $currentPage,
            ['path' => route('pet-history.index'), 'query' => $request->query(), 'fragment' => 'history-records']
        );
        $paginatedEvents->fragment('history-records');

        // All active pets for dropdown filter
        $allPets = Pet::select('id', 'name', 'breed', 'type')->orderBy('name')->get();

        return view('pet-history.index', [
            'events'                    => $paginatedEvents,
            'selectedYear'              => $selectedYear,
            'availableYears'            => $availableYears,
            'eventType'                 => $eventType,
            'species'                   => $species,
            'search'                    => $search,
            'petIdFilter'               => $petIdFilter,
            'allPets'                   => $allPets,
            'totalHistoricalPets'       => $totalHistoricalPets,
            'totalHistoricalAdoptions'  => $totalHistoricalAdoptions,
            'totalHistoricalMedicalLogs'=> $totalHistoricalMedicalLogs,
            'totalActiveInShelter'      => $totalActiveInShelter,
            'totalDogsCount'            => $totalDogsCount,
            'totalCatsCount'            => $totalCatsCount,
            'chartMonths'               => $chartMonths,
            'chartIntakes'              => $chartIntakes,
            'chartAdoptions'            => $chartAdoptions,
            'chartMedicals'             => $chartMedicals,
            'medicalCategoryBreakdown'  => $medicalCategoryBreakdown,
        ]);
    }
}
