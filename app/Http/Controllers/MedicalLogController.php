<?php

namespace App\Http\Controllers;

use App\Models\MedicalLog;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class MedicalLogController extends Controller
{
    public function index(): View
    {
        $q = request()->input('q');
        $filter = request()->input('filter', 'all');

        // Official Clinical Registry query: Only include internal shelter logs or verified external records
        $query = MedicalLog::with(['pet.medicalLogs' => function ($q) {
            $q->latest('date');
        }, 'creator'])
        ->where(function ($w) {
            $w->where('is_external', false)
              ->orWhereNull('is_external')
              ->orWhere(function ($ext) {
                  $ext->where('is_external', true)
                      ->where('verification_status', 'verified');
              });
        });

        if ($q) {
            $query->where(function ($b) use ($q) {
                $b->where('administered_by', 'like', "%{$q}%")
                    ->orWhere('clinic_name', 'like', "%{$q}%")
                    ->orWhere('vaccine_name', 'like', "%{$q}%")
                    ->orWhereHas('pet', function ($sub) use ($q) {
                        $sub->where('breed', 'like', "%{$q}%")
                            ->orWhere('color', 'like', "%{$q}%")
                            ->orWhere('name', 'like', "%{$q}%")
                            ->orWhere('id', 'like', "%{$q}%")
                            ->orWhere('type', 'like', "%{$q}%");
                    });
            });
        }

        // Official counts (internal shelter records or verified external records only)
        $officialLogsCount = function (?string $category = null) {
            $q = MedicalLog::where(function ($w) {
                $w->where('is_external', false)
                  ->orWhereNull('is_external')
                  ->orWhere(function ($ext) {
                      $ext->where('is_external', true)
                          ->where('verification_status', 'verified');
                  });
            });
            if ($category) {
                $q->where('category', $category);
            }
            return $q->count();
        };

        $totalLogsCount = $officialLogsCount();
        $vaccineCount = $officialLogsCount('vaccination');
        $dewormingCount = $officialLogsCount('deworming');
        $pendingVetProofsCount = MedicalLog::where('is_external', true)
            ->where('verification_status', 'pending')
            ->count();

        if ($filter === 'vaccination') {
            $query->where('category', 'vaccination');
        } elseif ($filter === 'deworming') {
            $query->where('category', 'deworming');
        }

        $logs = $query->latest('date')->paginate(15)->withQueryString();

        $pets = Pet::with(['medicalLogs' => function ($q) {
            $q->latest('date')->take(5);
        }])->orderBy('name')->orderBy('id')->get();

        return view('medical-logs.index', [
            'logs' => $logs,
            'pets' => $pets,
            'q' => $q,
            'filter' => $filter,
            'totalLogsCount' => $totalLogsCount,
            'vaccineCount' => $vaccineCount,
            'dewormingCount' => $dewormingCount,
            'pendingVetProofsCount' => $pendingVetProofsCount,
        ]);
    }

    public function create(?Pet $pet = null): View
    {
        if ((!$pet || !$pet->exists) && request()->filled('pet_id')) {
            $pet = Pet::find(request('pet_id'));
        }

        if ($pet && $pet->exists) {
            $pet->load(['medicalLogs' => function ($q) {
                $q->latest('date');
            }, 'medicalLogs.creator', 'addedBy']);
        }

        $pets = Pet::with(['medicalLogs' => function ($q) {
            $q->latest('date')->take(5);
        }])->orderBy('name')->orderBy('id')->get();

        $authorizedUsers = User::whereIn('role', ['admin', 'staff'])->orderBy('name')->get();

        return view('medical-logs.create', [
            'pet' => $pet,
            'pets' => $pets,
            'authorizedUsers' => $authorizedUsers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pet_id' => 'required|exists:pets,id',
            'date' => 'required|date',
            'category' => ['required', Rule::in(['vaccination', 'deworming'])],
            'vaccine_name' => 'nullable|string|max:120',
            'deworming_name' => 'nullable|string|max:120',
            'administered_by' => 'nullable|string|max:255',
            'next_due_date' => 'nullable|date',
        ]);

        $vaccineName = trim($request->input('vaccine_name', '')) ?: trim($request->input('deworming_name', ''));
        $data['vaccine_name'] = !empty($vaccineName) ? $vaccineName : null;
        unset($data['deworming_name']);

        $data['next_due_date'] = $this->calculateNextDueDate($data['category'], $data['date'], $data['next_due_date'] ?? null);

        $lockKey = 'med_log_store_lock_' . ($request->session()->getId() ?: (Auth::id() ?? $request->ip())) . '_' . ($data['pet_id'] ?? '');
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 3);

        if (! $lock->get()) {
            return redirect()->back()->with('success', 'Medical log entry is already being processed.');
        }

        try {
            $administeredBy = trim($request->input('administered_by', '')) ?: Auth::user()->name;
            $clinicianUser = User::where('name', $administeredBy)->first();
            $creatorId = $clinicianUser?->id ?: Auth::id();

            // If an entry already exists for this pet, category, and date, update it in-place to avoid duplicates
            $targetLogId = $request->input('log_id') ?: $request->input('medical_log_id');
            $existingLog = null;
            if ($targetLogId) {
                $existingLog = MedicalLog::where('id', $targetLogId)->first();
            }
            if (! $existingLog) {
                $existingLog = MedicalLog::where('pet_id', $data['pet_id'])
                    ->where('category', $data['category'])
                    ->where('date', $data['date'])
                    ->first();
            }

            if ($existingLog) {
                $existingLog->update([
                    ...$data,
                    'administered_by' => $administeredBy,
                ]);
                $log = $existingLog;
                $actionVerb = 'updated';
            } else {
                $log = MedicalLog::create([
                    ...$data,
                    'administered_by' => $administeredBy,
                    'created_by' => $creatorId,
                ]);
                $actionVerb = 'added';
            }

            // Fulfill and clear any other open booster schedules for this pet & category that are completed
            MedicalLog::where('pet_id', $data['pet_id'])
                ->where('category', $data['category'])
                ->where('id', '!=', $log->id)
                ->whereNotNull('next_due_date')
                ->whereDate('next_due_date', '<=', $data['date'])
                ->update(['next_due_date' => null]);

            $this->notifyAdoptersOfMedicalLog($log, $data['category']);

            $petName = $log->pet->name ?? ('Pet no. ' . $log->pet_id);
            session()->flash('success', "Medical log entry {$actionVerb} for {$petName}.");

            if ($request->filled('redirect_to')) {
                return redirect($request->input('redirect_to'));
            }

            return redirect()->route('medical-logs.index');
        } finally {
            $lock->release();
        }
    }

    public function edit(MedicalLog $medicalLog): View
    {
        $medicalLog->load(['pet.medicalLogs' => function ($q) {
            $q->latest('date');
        }, 'pet.addedBy']);

        $pets = Pet::with(['medicalLogs' => function ($q) {
            $q->latest('date')->take(5);
        }])->orderBy('name')->orderBy('id')->get();

        $authorizedUsers = User::whereIn('role', ['admin', 'staff'])->orderBy('name')->get();

        return view('medical-logs.edit', [
            'medicalLog' => $medicalLog,
            'pet' => $medicalLog->pet,
            'pets' => $pets,
            'authorizedUsers' => $authorizedUsers,
        ]);
    }

    public function update(Request $request, MedicalLog $medicalLog): RedirectResponse
    {
        $data = $request->validate([
            'pet_id' => 'required|exists:pets,id',
            'date' => 'required|date',
            'category' => ['required', Rule::in(['vaccination', 'deworming', 'treatment', 'checkup', 'surgery', 'injury_illness'])],
            'vaccine_name' => 'nullable|string|max:120',
            'deworming_name' => 'nullable|string|max:120',
            'administered_by' => 'nullable|string|max:255',
            'next_due_date' => 'nullable|date',
        ]);

        $vaccineName = trim($request->input('vaccine_name', '')) ?: trim($request->input('deworming_name', ''));
        if ($request->has('vaccine_name') || $request->has('deworming_name')) {
            $data['vaccine_name'] = !empty($vaccineName) ? $vaccineName : null;
        }
        unset($data['deworming_name']);

        $data['next_due_date'] = $this->calculateNextDueDate($data['category'], $data['date'], $data['next_due_date'] ?? null);

        if (!empty($data['administered_by'])) {
            $clinicianUser = User::where('name', $data['administered_by'])->first();
            if ($clinicianUser) {
                $data['created_by'] = $clinicianUser->id;
            }
        }

        $medicalLog->update($data);

        // Fulfill and clear prior open booster schedules for this pet & category that are now completed by this dose
        MedicalLog::where('pet_id', $data['pet_id'])
            ->where('category', $data['category'])
            ->where('id', '!=', $medicalLog->id)
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<=', $data['date'])
            ->update(['next_due_date' => null]);

        $this->notifyAdoptersOfMedicalLog($medicalLog, $data['category']);

        $petName = $medicalLog->pet->name ?? ('Pet no. ' . $medicalLog->pet_id);
        session()->flash('success', "Medical log entry updated for {$petName}.");

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'));
        }

        return redirect()->route('medical-logs.index');
    }

    private function notifyAdoptersOfMedicalLog(MedicalLog $log, string $category): void
    {
        $pet = $log->pet;
        $petName = ($pet && !empty($pet->name)) ? $pet->name : ('Pet no. ' . $log->pet_id);
        $categoryLabel = ucfirst(str_replace('_', ' ', $category));
        $itemDetail = !empty($log->vaccine_name) ? " ({$log->vaccine_name})" : '';
        $isDeworming = ($category === 'deworming');

        $dueDateStr = $log->next_due_date ? $log->next_due_date->format('M d, Y') : null;

        if ($log->is_external) {
            $title = $isDeworming
                ? "Deworming Verified for {$petName}!"
                : "Vaccination Verified for {$petName}!";

            if ($dueDateStr) {
                $body = $isDeworming
                    ? "Your clinic record for {$petName}{$itemDetail} has been verified by shelter staff. Next deworming dose due: {$dueDateStr}."
                    : "Your clinic record for {$petName}{$itemDetail} has been verified by shelter staff. Next vaccination booster due: {$dueDateStr}.";
            } else {
                $body = "Your clinic record for {$petName}{$itemDetail} has been verified by shelter staff.";
            }
        } else {
            $title = $isDeworming
                ? "Deworming Scheduled for {$petName}!"
                : "Vaccination Scheduled for {$petName}!";

            if ($dueDateStr) {
                $body = $isDeworming
                    ? "{$petName}'s Deworming{$itemDetail} record was updated. Next deworming dose due: {$dueDateStr}. Check CAWS app for details!"
                    : "{$petName}'s Vaccination{$itemDetail} record was updated. Next booster due: {$dueDateStr}. Check CAWS app for details!";
            } else {
                $body = "A new {$categoryLabel}{$itemDetail} record has been added for {$petName}. Check the CAWS app for details.";
            }
        }

        // Find verified applicants or adopters for this pet
        $adopterEmails = \App\Models\AdoptionApplication::where('pet_id', $log->pet_id)
            ->pluck('applicant_email');

        if ($log->creator && !empty($log->creator->email)) {
            $adopterEmails->push($log->creator->email);
        }

        $appUserIds = \App\Models\AdoptionApplication::where('pet_id', $log->pet_id)
            ->whereNotNull('user_id')
            ->pluck('user_id');
        if ($appUserIds->isNotEmpty()) {
            $userEmails = \App\Models\User::whereIn('id', $appUserIds)->pluck('email');
            $adopterEmails = $adopterEmails->merge($userEmails);
        }

        $adopterEmails = $adopterEmails->filter()->unique();

        foreach ($adopterEmails as $email) {
            \App\Services\FirebaseNotificationService::sendToUser(
                $email,
                $title,
                $body,
                [
                    'type'         => 'vaccine_reminder',
                    'pet_id'       => (string) $log->pet_id,
                    'category'     => (string) $category,
                    'vaccine_name' => (string) ($log->vaccine_name ?? ''),
                    'due_date'     => $log->next_due_date ? $log->next_due_date->format('Y-m-d') : '',
                ]
            );
        }
    }

    public function verify(Request $request, MedicalLog $medicalLog): RedirectResponse
    {
        $status = $request->input('status', 'verified');
        if (!in_array($status, ['verified', 'rejected'])) {
            $status = 'verified';
        }

        $pet = $medicalLog->pet;
        $petName = $pet?->name ?? ('Pet no. ' . $medicalLog->pet_id);
        $category = $medicalLog->category;

        if ($status === 'verified') {
            $dateStr = $medicalLog->date ? $medicalLog->date->format('Y-m-d') : now()->format('Y-m-d');
            $nextDueDate = $request->input('next_due_date');
            if (empty($nextDueDate)) {
                $nextDueDate = $this->calculateNextDueDate($category, $dateStr, null);
            }

            $medicalLog->update([
                'verification_status' => 'verified',
                'next_due_date'       => $nextDueDate,
                'verified_by'         => Auth::id(),
                'verified_at'         => now(),
            ]);
            $medicalLog->refresh();

            // Fulfill prior open booster schedules for this pet & category completed by this dose
            MedicalLog::where('pet_id', $medicalLog->pet_id)
                ->where('category', $category)
                ->where('id', '!=', $medicalLog->id)
                ->whereNotNull('next_due_date')
                ->whereDate('next_due_date', '<=', $medicalLog->date)
                ->update(['next_due_date' => null]);

            $this->notifyAdoptersOfMedicalLog($medicalLog, $category);

            $dueFormatted = $medicalLog->next_due_date ? ' Next booster scheduled for ' . $medicalLog->next_due_date->format('M d, Y') . '.' : '';
            session()->flash('success', "External {$category} record for {$petName} was verified.{$dueFormatted}");
        } else {
            $reason = trim($request->input('rejection_reason', 'The uploaded photo was unclear or missing clinic verification.'));
            
            $medicalLog->update([
                'verification_status' => 'rejected',
                'verified_by'         => Auth::id(),
                'verified_at'         => now(),
            ]);

            $adopterEmail = $medicalLog->creator?->email 
                ?? \App\Models\AdoptionApplication::where('pet_id', $medicalLog->pet_id)->value('applicant_email');

            if ($adopterEmail) {
                \App\Services\FirebaseNotificationService::sendToUser(
                    $adopterEmail,
                    "Veterinary Record Update for {$petName}",
                    "Your {$category} submission could not be verified: {$reason}. Please check the app to review and re-upload.",
                    [
                        'type'   => 'vet_log_rejected',
                        'pet_id' => (string) $medicalLog->pet_id,
                        'log_id' => (string) $medicalLog->id,
                    ]
                );
            }

            session()->flash('success', "External {$category} record for {$petName} was rejected. Adopter has been notified.");
        }

        return redirect()->back();
    }

    public function destroy(MedicalLog $medicalLog): RedirectResponse
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only an admin can delete medical log entries.');
        }

        $medicalLog->delete();

        session()->flash('success', 'Medical log entry removed.');

        return redirect()->back();
    }

    private function calculateNextDueDate(string $category, string $date, ?string $manualNextDueDate): ?string
    {
        if (!empty($manualNextDueDate)) {
            return $manualNextDueDate;
        }

        // Automatic scheduling if left empty
        if ($category === 'vaccination') {
            return Carbon::parse($date)->addMonths(6)->format('Y-m-d');
        }

        if ($category === 'deworming') {
            return Carbon::parse($date)->addMonths(3)->format('Y-m-d');
        }

        return null;
    }
}
