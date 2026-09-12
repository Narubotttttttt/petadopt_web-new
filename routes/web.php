<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MedicalLogController;
use App\Http\Controllers\PetHistoryController;
use App\Http\Controllers\ReportController;
use App\Models\AdoptionApplication;
use App\Models\MedicalLog;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pets', [PetController::class, 'index'])->name('pets.index');
    Route::get('/pets/{pet}', [PetController::class, 'show'])->name('pets.show')->whereNumber('pet');
});

Route::middleware(['auth', 'verified', 'staff'])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $selectedYear = (int) $request->query('year', now()->year);

        $isSqlite = DB::getDriverName() === 'sqlite';
        $monthAdoption = $isSqlite ? "CAST(strftime('%m', COALESCE(approved_at, updated_at)) AS INTEGER)" : 'MONTH(COALESCE(approved_at, updated_at))';
        $yearAdoption  = $isSqlite ? "CAST(strftime('%Y', COALESCE(approved_at, updated_at)) AS INTEGER)" : 'YEAR(COALESCE(approved_at, updated_at))';
        $monthIntake   = $isSqlite ? "CAST(strftime('%m', created_at) AS INTEGER)" : 'MONTH(created_at)';
        $yearIntake    = $isSqlite ? "CAST(strftime('%Y', created_at) AS INTEGER)" : 'YEAR(created_at)';
        $monthMedical  = $isSqlite ? "CAST(strftime('%m', date) AS INTEGER)" : 'MONTH(date)';
        $yearMedical   = $isSqlite ? "CAST(strftime('%Y', date) AS INTEGER)" : 'YEAR(date)';

        // 1. Adoption Trends
        $adoptionTrends = AdoptionApplication::selectRaw("{$monthAdoption} as month, COUNT(*) as total")
            ->where('status', 'approved')
            ->whereRaw("{$yearAdoption} = ?", [$selectedYear])
            ->groupBy('month')
            ->pluck('total', 'month');

        // 2. Pet Intake History Trends
        $intakeTrends = Pet::selectRaw("{$monthIntake} as month, COUNT(*) as total")
            ->whereRaw("{$yearIntake} = ?", [$selectedYear])
            ->groupBy('month')
            ->pluck('total', 'month');

        // 3. Clinical Medical History Trends
        $medicalTrends = MedicalLog::selectRaw("{$monthMedical} as month, COUNT(*) as total")
            ->whereRaw("{$yearMedical} = ?", [$selectedYear])
            ->groupBy('month')
            ->pluck('total', 'month');

        $chartMonths = [];
        $chartCounts = [];
        $chartIntakes = [];
        $chartMedicals = [];

        foreach (range(1, 12) as $m) {
            $chartMonths[] = Carbon::create()->month($m)->format('M');
            $chartCounts[] = (int) ($adoptionTrends->get($m, 0));
            $chartIntakes[] = (int) ($intakeTrends->get($m, 0));
            $chartMedicals[] = (int) ($medicalTrends->get($m, 0));
        }

        // Available years for dropdown
        $adoptionYears = AdoptionApplication::where('status', 'approved')
            ->selectRaw("DISTINCT {$yearAdoption} as yr")
            ->pluck('yr')
            ->map(fn($y) => (int)$y)
            ->toArray();

        $intakeYears = Pet::selectRaw("DISTINCT {$yearIntake} as yr")
            ->pluck('yr')
            ->map(fn($y) => (int)$y)
            ->toArray();

        $availableYears = array_values(array_unique(array_merge([now()->year], $adoptionYears, $intakeYears)));
        rsort($availableYears);

        $totalYearAdoptions = array_sum($chartCounts);
        $totalYearIntakes = array_sum($chartIntakes);
        $totalYearMedicals = array_sum($chartMedicals);
        $totalYearApplications = AdoptionApplication::whereRaw("{$yearAdoption} = ?", [$selectedYear])->count();
        $yearConversionRate = $totalYearApplications > 0 ? round(($totalYearAdoptions / $totalYearApplications) * 100, 1) : 0;

        $peakCount = !empty($chartCounts) ? max($chartCounts) : 0;
        $peakMonthIdx = array_search($peakCount, $chartCounts);
        $peakMonth = ($peakCount > 0 && $peakMonthIdx !== false) ? $chartMonths[$peakMonthIdx] : 'None';

        // Historical status breakdown
        $statusBreakdown = [
            'available' => Pet::where('status', 'available')->count(),
            'pending'   => Pet::where('status', 'pending')->count(),
            'adopted'   => Pet::where('status', 'adopted')->count(),
        ];

        return view('dashboard', [
            'totalPets'              => Pet::count(),
            'totalUsers'             => User::whereIn('role', ['admin', 'staff'])->count(),
            'totalAdoptions'         => AdoptionApplication::where('status', 'approved')->count(),
            'latestPet'              => Pet::latest('created_at')->first(),
            'recentApplications'     => AdoptionApplication::with(['pet', 'user.adoptersProfile'])->latest('created_at')->take(5)->get(),
            'chartMonths'            => $chartMonths,
            'chartCounts'            => $chartCounts,
            'chartIntakes'           => $chartIntakes,
            'chartMedicals'          => $chartMedicals,
            'selectedYear'           => $selectedYear,
            'availableYears'         => $availableYears,
            'totalYearAdoptions'     => $totalYearAdoptions,
            'totalYearIntakes'       => $totalYearIntakes,
            'totalYearMedicals'      => $totalYearMedicals,
            'totalYearApplications'  => $totalYearApplications,
            'yearConversionRate'     => $yearConversionRate,
            'peakMonth'              => $peakMonth,
            'peakCount'              => $peakCount,
            'statusBreakdown'        => $statusBreakdown,
        ]);
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/signature', [ProfileController::class, 'updateSignature'])->name('profile.signature.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/pets/create', [PetController::class, 'create'])->name('pets.create');
    Route::post('/pets', [PetController::class, 'store'])->name('pets.store');
    Route::get('/pets/{pet}/edit', [PetController::class, 'edit'])->name('pets.edit');
    Route::match(['put','patch'],'/pets/{pet}', [PetController::class, 'update'])->name('pets.update');
    Route::delete('/pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

    Route::get('/adoption-applications', [\App\Http\Controllers\AdoptionApplicationController::class, 'index'])->name('adoption-applications.index');
    Route::get('/adoption-applications/{application}', [\App\Http\Controllers\AdoptionApplicationController::class, 'show'])->name('adoption-applications.show');
    Route::patch('/adoption-applications/{application}', [\App\Http\Controllers\AdoptionApplicationController::class, 'update'])->name('adoption-applications.update');
    Route::post('/adoption-applications/{application}/sign-as-staff', [\App\Http\Controllers\AdoptionApplicationController::class, 'signAsStaff'])->name('adoption-applications.sign-as-staff');
    Route::get('/adoption-applications/{application}/contract', [\App\Http\Controllers\AdoptionApplicationController::class, 'downloadContract'])->name('adoption-applications.contract');

    Route::get('/adopters', [\App\Http\Controllers\AdopterProfileController::class, 'index'])->name('adopters.index');
    Route::patch('/adopters/{id}/status', [\App\Http\Controllers\AdopterProfileController::class, 'updateStatus'])->name('adopters.update-status');

    Route::get('/medical-logs', [MedicalLogController::class, 'index'])->name('medical-logs.index');
    Route::get('/medical-logs/create', [MedicalLogController::class, 'create'])->name('medical-logs.create');
    Route::post('/medical-logs', [MedicalLogController::class, 'store'])->name('medical-logs.store');
    Route::get('/pets/{pet}/medical-logs/create', [MedicalLogController::class, 'create'])->name('medical-logs.create-for-pet');
    Route::get('/medical-logs/{medicalLog}/edit', [MedicalLogController::class, 'edit'])->name('medical-logs.edit');
    Route::match(['put', 'patch'], '/medical-logs/{medicalLog}', [MedicalLogController::class, 'update'])->name('medical-logs.update');
    Route::delete('/medical-logs/{medicalLog}', [MedicalLogController::class, 'destroy'])->name('medical-logs.destroy');

    Route::get('/pet-history', [PetHistoryController::class, 'index'])->name('pet-history.index');

    // System Reports & Analytics
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');

    // Admin Notification Routes
    Route::get('/admin/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::post('/admin/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('admin.notifications.markAllRead');
    Route::post('/admin/notifications/mark-read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('admin.notifications.markRead');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/staff-profile', [UserController::class, 'updateStaffProfile'])->name('users.update-staff-profile');
});

// Public signed route for contract downloads — accessible by mobile browsers without web session
Route::get('/contract/{id}/download', [\App\Http\Controllers\AdoptionApplicationController::class, 'downloadContract'])->name('contract.download')->middleware('signed');

require __DIR__.'/auth.php';