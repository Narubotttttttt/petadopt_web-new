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

        MedicalLog::create([
            ...$data,
            'administered_by' => Auth::user()->name,
            'created_by' => Auth::id(),
        ]);

        session()->flash('success', 'Medical log entry added.');

        return redirect()->route('medical-logs.index');
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

        session()->flash('success', 'Medical log entry updated.');

        return redirect()->route('medical-logs.index');
    }

    public function destroy(MedicalLog $medicalLog): RedirectResponse
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only an admin can delete medical log entries.');
        }

        $medicalLog->delete();

        session()->flash('success', 'Medical log entry removed.');

        return redirect()->route('medical-logs.index');
    }

    private function calculateNextDueDate(string $category, string $date, ?string $manualNextDueDate): ?string
    {
        if ($category === 'vaccination') {
            return Carbon::parse($date)->addMonths(6)->format('Y-m-d');
        }

        return $manualNextDueDate;
    }
}