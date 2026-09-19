<?php

namespace App\Http\Controllers;

use App\Models\AdopterPreference;
use App\Models\AdoptersProfile;
use App\Models\AdoptionApplication;
use App\Models\MedicalLog;
use App\Models\Pet;
use App\Models\PetHealthUpdate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the reports and analytics dashboard with interactive filters.
     */
    public function index(Request $request)
    {
        $reportData = $this->generateReportData($request);

        return view('reports.index', $reportData);
    }

    /**
     * Export the filtered report as an official branded PDF document.
     * Accessible exclusively by administrators.
     */
    public function exportPdf(Request $request)
    {
        abort_if(Auth::user()?->role !== 'admin', 403, 'Unauthorized. Only administrators are authorized to export PDF reports.');

        $reportData = $this->generateReportData($request, true);

        $currentUser = Auth::user();
        $generatedByName = $currentUser ? $currentUser->name : 'CAWS Authorized Staff';
        $generatedByRole = $currentUser ? ucfirst($currentUser->role) : 'Administrator';

        // Load organization logo base64 if available for DomPDF embedding
        $logoBase64 = null;
        $logoPath = public_path('images/caws-logo.png');
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        $pdf = Pdf::loadView('pdf.report', array_merge($reportData, [
            'generatedByName' => $generatedByName,
            'generatedByRole' => $generatedByRole,
            'generatedAt'     => now()->format('F d, Y h:i A'),
            'logoBase64'      => $logoBase64,
        ]))->setPaper('a4', 'portrait');

        $filename = 'CAWS_' . ucfirst($reportData['reportType']) . '_Report_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Compute and aggregate data, charts, stats, and records for the selected report type and filters.
     */
    private function generateReportData(Request $request, bool $isExport = false): array
    {
        $reportType = $request->query('type', 'overview'); // overview, adoptions, intakes, medical, compliance
        $preset     = $request->query('preset', 'this_year'); // this_month, last_month, last_3_months, this_year, last_year, all_time, custom
        $startDateParam = $request->query('start_date');
        $endDateParam   = $request->query('end_date');
        $species    = $request->query('species', 'all'); // all, dog, cat
        $status     = $request->query('status', 'all');
        $search     = trim((string) $request->query('q', ''));

        // 1. Calculate Date Range Bounds
        $now = now();
        switch ($preset) {
            case 'this_month':
                $startDate = $now->copy()->startOfMonth();
                $endDate   = $now->copy()->endOfMonth();
                $dateRangeLabel = 'This Month (' . $startDate->format('M Y') . ')';
                break;

            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate   = $now->copy()->subMonth()->endOfMonth();
                $dateRangeLabel = 'Last Month (' . $startDate->format('M Y') . ')';
                break;

            case 'last_3_months':
                $startDate = $now->copy()->subMonths(2)->startOfMonth();
                $endDate   = $now->copy()->endOfMonth();
                $dateRangeLabel = 'Last 3 Months (' . $startDate->format('M Y') . ' - ' . $endDate->format('M Y') . ')';
                break;

            case 'last_year':
                $startDate = $now->copy()->subYear()->startOfYear();
                $endDate   = $now->copy()->subYear()->endOfYear();
                $dateRangeLabel = 'Last Year (' . $startDate->format('Y') . ')';
                break;

            case 'all_time':
                $startDate = Carbon::create(2020, 1, 1, 0, 0, 0);
                $endDate   = $now->copy()->endOfDay();
                $dateRangeLabel = 'All Time Records';
                break;

            case 'custom':
                $startDate = !empty($startDateParam) ? Carbon::parse($startDateParam)->startOfDay() : $now->copy()->startOfYear();
                $endDate   = !empty($endDateParam) ? Carbon::parse($endDateParam)->endOfDay() : $now->copy()->endOfDay();
                $dateRangeLabel = 'Custom Range (' . $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y') . ')';
                break;

            case 'this_year':
            default:
                $preset = 'this_year';
                $startDate = $now->copy()->startOfYear();
                $endDate   = $now->copy()->endOfYear();
                $dateRangeLabel = 'This Year (' . $startDate->format('Y') . ')';
                break;
        }

        $stats = [];
        $records = [];
        $chartData = [];



        // 2. Fetch specific dataset based on reportType
        switch ($reportType) {
            case 'adoptions':
                $query = AdoptionApplication::with(['pet', 'staff', 'evaluator'])
                    ->whereBetween('created_at', [$startDate, $endDate]);

                if ($species !== 'all') {
                    $query->whereHas('pet', fn($q) => $q->where('type', $species));
                }

                if ($status !== 'all') {
                    $query->where('status', $status);
                }

                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('applicant_name', 'like', "%{$search}%")
                          ->orWhere('applicant_email', 'like', "%{$search}%")
                          ->orWhere('applicant_phone', 'like', "%{$search}%")
                          ->orWhereHas('pet', fn($p) => $p->where('name', 'like', "%{$search}%"));
                    });
                }

                $totalApplications = (clone $query)->count();
                $approvedCount     = (clone $query)->where('status', 'approved')->count();
                $underReviewCount  = (clone $query)->where('status', 'under_review')->count();
                $pendingCount      = (clone $query)->where('status', 'pending')->count();
                $rejectedCount     = (clone $query)->where('status', 'rejected')->count();
                $approvalRate      = $totalApplications > 0 ? round(($approvedCount / $totalApplications) * 100, 1) : 0;

                $stats = [
                    'totalApplications' => $totalApplications,
                    'approvedCount'     => $approvedCount,
                    'underReviewCount'  => $underReviewCount,
                    'pendingCount'      => $pendingCount,
                    'rejectedCount'     => $rejectedCount,
                    'approvalRate'      => $approvalRate,
                ];



                $records = $isExport ? $query->latest('created_at')->get() : $query->latest('created_at')->paginate(20)->withQueryString();
                break;

            case 'intakes':
                $query = Pet::with('addedBy')
                    ->whereBetween('created_at', [$startDate, $endDate]);

                if ($species !== 'all') {
                    $query->where('type', $species);
                }

                if ($status !== 'all') {
                    $query->where('status', $status);
                }

                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('breed', 'like', "%{$search}%")
                          ->orWhere('color', 'like', "%{$search}%");
                    });
                }

                $totalIntakes   = (clone $query)->count();
                $dogCount       = (clone $query)->where('type', 'dog')->count();
                $catCount       = (clone $query)->where('type', 'cat')->count();
                $availableCount = (clone $query)->where('status', 'available')->count();
                $adoptedCount   = (clone $query)->where('status', 'adopted')->count();
                $pendingCount   = (clone $query)->where('status', 'pending')->count();

                $stats = [
                    'totalIntakes'   => $totalIntakes,
                    'dogCount'       => $dogCount,
                    'catCount'       => $catCount,
                    'availableCount' => $availableCount,
                    'adoptedCount'   => $adoptedCount,
                    'pendingCount'   => $pendingCount,
                ];



                $records = $isExport ? $query->latest('created_at')->get() : $query->latest('created_at')->paginate(20)->withQueryString();
                break;

            case 'medical':
                $query = MedicalLog::with(['pet', 'creator'])
                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

                if ($species !== 'all') {
                    $query->whereHas('pet', fn($q) => $q->where('type', $species));
                }

                if ($status !== 'all') {
                    $query->where('category', $status);
                }

                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('administered_by', 'like', "%{$search}%")
                          ->orWhere('category', 'like', "%{$search}%")
                          ->orWhereHas('pet', fn($p) => $p->where('name', 'like', "%{$search}%"));
                    });
                }

                $totalMedicals = (clone $query)->count();
                $vaccinationCount = (clone $query)->where('category', 'like', '%vaccin%')->count();
                $surgeryCount     = (clone $query)->where('category', 'like', '%surg%')->count();
                $dewormingCount   = (clone $query)->where('category', 'like', '%deworm%')->count();
                $checkupCount     = (clone $query)->where(function ($q) {
                    $q->where('category', 'like', '%check%')
                      ->orWhere('category', 'like', '%routine%');
                })->count();

                $stats = [
                    'totalMedicals'    => $totalMedicals,
                    'vaccinationCount' => $vaccinationCount,
                    'surgeryCount'     => $surgeryCount,
                    'dewormingCount'   => $dewormingCount,
                    'checkupCount'     => $checkupCount,
                ];



                $records = $isExport ? $query->latest('date')->get() : $query->latest('date')->paginate(20)->withQueryString();
                break;

            case 'compliance':
                $adoptersQuery = AdoptersProfile::with(['user', 'adoptionApplications' => fn($q) => $q->where('status', 'approved')->with('pet')]);

                if (!empty($search)) {
                    $adoptersQuery->where(function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%")
                          ->orWhere('city', 'like', "%{$search}%")
                          ->orWhere('adopter_code', 'like', "%{$search}%");
                    });
                }

                $allAdopters = $adoptersQuery->get();
                $processedList = [];
                $counts = [
                    'total'        => 0,
                    'submitted'    => 0,
                    'due_soon'     => 0,
                    'overdue'      => 0,
                    'pending_first'=> 0,
                ];

                $today = now()->startOfDay();

                foreach ($allAdopters as $adopter) {
                    $approvedApps = $adopter->adoptionApplications;
                    $adoptedCount = $approvedApps->count();

                    // Determine compliance status
                    $lastCheckIn = $adopter->last_check_in_date;
                    $statusType = 'pending_first';
                    $statusLabel = 'Pending 1st Check-In';

                    if ($lastCheckIn) {
                        $nextDueDate = $lastCheckIn->copy()->addDays(30)->startOfDay();
                        $diffInDays = (int) $today->diffInDays($nextDueDate, false);

                        if ($diffInDays < 0) {
                            $statusType = 'overdue';
                            $statusLabel = 'Overdue (' . abs($diffInDays) . 'd)';
                        } elseif ($diffInDays <= 7) {
                            $statusType = 'due_soon';
                            $statusLabel = 'Due Soon (' . $diffInDays . 'd)';
                        } else {
                            $statusType = 'submitted';
                            $statusLabel = 'Up to Date (' . $diffInDays . 'd left)';
                        }
                    }

                    if ($status !== 'all' && $status !== $statusType) {
                        continue;
                    }

                    $counts['total']++;
                    $counts[$statusType]++;

                    $processedList[] = [
                        'id'                 => $adopter->id,
                        'adopter_code'       => $adopter->adopter_code ?? ('ADOPT-' . str_pad((string)$adopter->id, 4, '0', STR_PAD_LEFT)),
                        'full_name'          => $adopter->full_name ?? ($adopter->user ? $adopter->user->name : 'N/A'),
                        'email'              => $adopter->email ?? ($adopter->user ? $adopter->user->email : 'N/A'),
                        'phone'              => $adopter->phone ?? 'N/A',
                        'location'           => trim(($adopter->city ?? '') . ', ' . ($adopter->province ?? ''), ', ') ?: 'Cagayan de Oro City',
                        'adopted_count'      => $adoptedCount,
                        'status_type'        => $statusType,
                        'status_label'       => $statusLabel,
                        'last_check_in_date' => $lastCheckIn ? $lastCheckIn->format('M d, Y') : 'No Check-Ins Yet',
                        'admin_notes'        => $adopter->admin_notes ?? 'None',
                        'avatar_url'         => $adopter->avatar_url,
                        'initials'           => $adopter->initials,
                    ];
                }

                $goodStandingRate = $counts['total'] > 0 ? round((($counts['submitted'] + $counts['due_soon']) / $counts['total']) * 100, 1) : 100;

                $stats = [
                    'totalAdopters'     => $counts['total'],
                    'submittedCount'    => $counts['submitted'],
                    'dueSoonCount'      => $counts['due_soon'],
                    'overdueCount'      => $counts['overdue'],
                    'pendingFirstCount' => $counts['pending_first'],
                    'goodStandingRate'  => $goodStandingRate,
                ];



                if ($isExport) {
                    $records = $processedList;
                } else {
                    $page = (int) $request->query('page', 1);
                    $perPage = 20;
                    $offset = ($page - 1) * $perPage;
                    $records = new \Illuminate\Pagination\LengthAwarePaginator(
                        array_slice($processedList, $offset, $perPage),
                        count($processedList),
                        $perPage,
                        $page,
                        ['path' => $request->url(), 'query' => $request->query()]
                    );
                }
                break;

            case 'overview':
            default:
                $reportType = 'overview';

                $totalIntakes = Pet::whereBetween('created_at', [$startDate, $endDate])->count();
                $dogIntakes   = Pet::where('type', 'dog')->whereBetween('created_at', [$startDate, $endDate])->count();
                $catIntakes   = Pet::where('type', 'cat')->whereBetween('created_at', [$startDate, $endDate])->count();

                $totalAdoptions       = AdoptionApplication::where('status', 'approved')
                    ->whereBetween('approved_at', [$startDate, $endDate])
                    ->count();
                $totalApplications    = AdoptionApplication::whereBetween('created_at', [$startDate, $endDate])->count();
                $underReviewAdoptions = AdoptionApplication::where('status', 'under_review')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();
                $pendingAdoptions     = AdoptionApplication::where('status', 'pending')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();
                $rejectedAdoptions    = AdoptionApplication::where('status', 'rejected')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                $totalMedicals    = MedicalLog::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->count();
                $vaccinationCount = MedicalLog::where('category', 'like', '%vaccin%')
                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->count();
                $surgeryCount     = MedicalLog::where('category', 'like', '%surg%')
                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->count();
                $checkupCount     = MedicalLog::where(function ($q) {
                    $q->where('category', 'like', '%check%')
                      ->orWhere('category', 'like', '%routine%')
                      ->orWhere('category', 'like', '%deworm%');
                })->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->count();

                $activeShelter = Pet::whereIn('status', ['available', 'pending'])->count();
                $totalDogs     = Pet::where('type', 'dog')->whereIn('status', ['available', 'pending'])->count();
                $totalCats     = Pet::where('type', 'cat')->whereIn('status', ['available', 'pending'])->count();

                $conversionRate = $totalApplications > 0 ? round(($totalAdoptions / $totalApplications) * 100, 1) : 0;

                $stats = [
                    'totalIntakes'         => $totalIntakes,
                    'dogIntakes'           => $dogIntakes,
                    'catIntakes'           => $catIntakes,
                    'approvedAdoptions'    => $totalAdoptions,
                    'totalApplications'    => $totalApplications,
                    'underReviewAdoptions' => $underReviewAdoptions,
                    'pendingAdoptions'     => $pendingAdoptions,
                    'rejectedAdoptions'    => $rejectedAdoptions,
                    'conversionRate'       => $conversionRate,
                    'totalMedicals'        => $totalMedicals,
                    'vaccinationCount'     => $vaccinationCount,
                    'surgeryCount'         => $surgeryCount,
                    'checkupCount'         => $checkupCount,
                    'activeShelter'        => $activeShelter,
                    'totalDogs'            => $totalDogs,
                    'totalCats'            => $totalCats,
                ];

                // Key milestone records for the overview table
                $recordsQuery = AdoptionApplication::with(['pet', 'staff'])
                    ->where('status', 'approved')
                    ->whereBetween('approved_at', [$startDate, $endDate])
                    ->latest('approved_at');

                $records = $isExport ? $recordsQuery->get() : $recordsQuery->paginate(20)->withQueryString();
                break;
        }

        return [
            'reportType'     => $reportType,
            'preset'         => $preset,
            'startDate'      => $startDate->format('Y-m-d'),
            'endDate'        => $endDate->format('Y-m-d'),
            'dateRangeLabel' => $dateRangeLabel,
            'species'        => $species,
            'status'         => $status,
            'search'         => $search,
            'stats'          => $stats,
            'records'        => $records,
            'chartData'      => $chartData,
        ];
    }
}
