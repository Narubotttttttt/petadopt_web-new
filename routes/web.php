<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MedicalLogController;
use App\Models\AdoptionApplication;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pets', [PetController::class, 'index'])->name('pets.index');
    Route::get('/pets/{pet}', [PetController::class, 'show'])->name('pets.show')->whereNumber('pet');
});

Route::middleware(['auth', 'verified', 'staff'])->group(function () {
    Route::get('/dashboard', function () {
        $adoptionTrends = AdoptionApplication::selectRaw('MONTH(approved_at) as month, COUNT(*) as total')
            ->whereNotNull('approved_at')
            ->whereYear('approved_at', now()->year)
            ->groupBy('month')
            ->pluck('total', 'month');

        $chartMonths = [];
        $chartCounts = [];

        foreach (range(1, 12) as $m) {
            $chartMonths[] = Carbon::create()->month($m)->format('M');
            $chartCounts[] = $adoptionTrends->get($m, 0);
        }

        return view('dashboard', [
            'totalPets' => Pet::count(),
            'totalUsers' => User::whereIn('role', ['admin', 'staff'])->count(),
            'latestPet' => Pet::latest('created_at')->first(),
            'chartMonths' => $chartMonths,
            'chartCounts' => $chartCounts,
        ]);
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/pets/create', [PetController::class, 'create'])->name('pets.create');
    Route::post('/pets', [PetController::class, 'store'])->name('pets.store');
    Route::get('/pets/{pet}/edit', [PetController::class, 'edit'])->name('pets.edit');
    Route::match(['put','patch'],'/pets/{pet}', [PetController::class, 'update'])->name('pets.update');
    Route::delete('/pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

    Route::get('/adoption-applications', [\App\Http\Controllers\AdoptionApplicationController::class, 'index'])->name('adoption-applications.index');
    Route::get('/adoption-applications/{application}', [\App\Http\Controllers\AdoptionApplicationController::class, 'show'])->name('adoption-applications.show');
    Route::patch('/adoption-applications/{application}', [\App\Http\Controllers\AdoptionApplicationController::class, 'update'])->name('adoption-applications.update');

    Route::get('/adopters', [\App\Http\Controllers\AdopterProfileController::class, 'index'])->name('adopters.index');

    Route::get('/medical-logs', [MedicalLogController::class, 'index'])->name('medical-logs.index');
    Route::get('/medical-logs/create', [MedicalLogController::class, 'create'])->name('medical-logs.create');
    Route::post('/medical-logs', [MedicalLogController::class, 'store'])->name('medical-logs.store');
    Route::get('/pets/{pet}/medical-logs/create', [MedicalLogController::class, 'create'])->name('medical-logs.create-for-pet');
    Route::get('/medical-logs/{medicalLog}/edit', [MedicalLogController::class, 'edit'])->name('medical-logs.edit');
    Route::match(['put', 'patch'], '/medical-logs/{medicalLog}', [MedicalLogController::class, 'update'])->name('medical-logs.update');
    Route::delete('/medical-logs/{medicalLog}', [MedicalLogController::class, 'destroy'])->name('medical-logs.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});

require __DIR__.'/auth.php';