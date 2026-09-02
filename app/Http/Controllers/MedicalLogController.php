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

        $query = MedicalLog::with('pet', 'creator');

        if ($q) {
            $query->whereHas('pet', function ($sub) use ($q) {
                $sub->where('breed', 'like', "%{$q}%")
                    ->orWhere('color', 'like', "%{$q}%")
                    ->orWhere('type', 'like', "%{$q}%");
            });
        }

        $logs = $query->latest('date')->paginate(15)->withQueryString();

        return view('medical-logs.index', [
            'logs' => $logs,
            'q' => $q,
        ]);
    }

    public function create(?Pet $pet = null): View
    {
        $pets = Pet::orderBy('breed')->get();

        return view('medical-logs.create', [
            'pet' => $pet,
            'pets' => $pets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pet_id' => 'required|exists:pets,id',
            'date' => 'required|date',
            'category' => ['required', Rule::in(['vaccination', 'deworming', 'treatment', 'checkup', 'surgery', 'injury_illness'])],
            'next_due_date' => 'nullable|date',
        ]);

        $data['next_due_date'] = $this->calculateNextDueDate($data['category'], $data['date'], $data['next_due_date'] ?? null);

        $lockKey = 'med_log_store_lock_' . (Auth::id() ?? $request->ip()) . '_' . ($data['pet_id'] ?? '');
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);

        if (! $lock->get()) {
            return redirect()->back()->with('success', 'Medical log entry is already being processed.');
        }

        try {
            $administeredBy = trim($request->input('administered_by', '')) ?: Auth::user()->name;

            $log = MedicalLog::create([
                ...$data,
                'administered_by' => $administeredBy,
                'created_by' => Auth::id(),
            ]);

            $this->notifyAdoptersOfMedicalLog($log, $data['category']);

            $petName = $log->pet->name ?? ('Pet no. ' . $log->pet_id);
            session()->flash('success', "Medical log entry added for {$petName}.");

            if ($request->filled('redirect_to')) {
                return redirect($request->input('redirect_to'));
            }

            return redirect()->back();
        } finally {
            $lock->release();
        }
    }

    public function edit(MedicalLog $medicalLog): View
    {
        $pets = Pet::orderBy('breed')->get();

        return view('medical-logs.edit', [
            'medicalLog' => $medicalLog,
            'pets' => $pets,
        ]);
    }

    public function update(Request $request, MedicalLog $medicalLog): RedirectResponse
    {
        $data = $request->validate([
            'pet_id' => 'required|exists:pets,id',
            'date' => 'required|date',
            'category' => ['required', Rule::in(['vaccination', 'deworming', 'treatment', 'checkup', 'surgery', 'injury_illness'])],
            'administered_by' => 'nullable|string|max:255',
            'next_due_date' => 'nullable|date',
        ]);

        $data['next_due_date'] = $this->calculateNextDueDate($data['category'], $data['date'], $data['next_due_date'] ?? null);

        $medicalLog->update($data);

        $this->notifyAdoptersOfMedicalLog($medicalLog, $data['category']);

        $petName = $medicalLog->pet->name ?? ('Pet no. ' . $medicalLog->pet_id);
        session()->flash('success', "Medical log entry updated for {$petName}.");

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'));
        }

        return redirect()->back();
    }

    private function notifyAdoptersOfMedicalLog(MedicalLog $log, string $category): void
    {
        $pet = $log->pet;
        $petName = ($pet && !empty($pet->name)) ? $pet->name : ('Pet no. ' . $log->pet_id);
        $categoryLabel = ucfirst(str_replace('_', ' ', $category));

        $title = $category === 'vaccination'
            ? "Vaccination Scheduled for {$petName}!"
            : "{$categoryLabel} Logged for {$petName}";

        $dueDateStr = $log->next_due_date ? $log->next_due_date->format('M d, Y') : null;
        $body = $dueDateStr
            ? "{$petName}'s {$categoryLabel} record was updated. Next due date: {$dueDateStr}. Check CAWS app for details!"
            : "A new {$categoryLabel} record has been added for {$petName}. Check the CAWS app for details.";

        // Find verified applicants or adopters for this pet
        $adopterEmails = \App\Models\AdoptionApplication::where('pet_id', $log->pet_id)
            ->pluck('applicant_email')
            ->unique();

        foreach ($adopterEmails as $email) {
            \App\Services\FirebaseNotificationService::sendToUser(
                $email,
                $title,
                $body,
                [
                    'type' => 'vaccine_reminder',
                    'pet_id' => $log->pet_id,
                    'category' => $category,
                ]
            );
        }
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

        // Deworming is optional (only set if manualNextDueDate is explicitly provided)
        return null;
    }
}
