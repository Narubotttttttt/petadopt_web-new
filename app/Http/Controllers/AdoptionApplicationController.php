<?php

namespace App\Http\Controllers;

use App\Models\AdoptionApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class AdoptionApplicationController extends Controller
{
    public function index(): View
    {
        $applications = AdoptionApplication::with('pet')->latest()->paginate(10);

        return view('adoption-applications.index', compact('applications'));
    }

    public function show(AdoptionApplication $application): View
    {
        return view('adoption-applications.show', compact('application'));
    }

    public function update(Request $request, AdoptionApplication $application): RedirectResponse
    {
        $isAdmin = Auth::user()->role === 'admin';

        $allowedStatuses = $isAdmin
            ? ['under_review', 'pending', 'approved', 'rejected']
            : ['under_review', 'pending', 'rejected'];

        $request->validate([
            'status' => ['required', Rule::in($allowedStatuses)],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $data = $request->only(['status', 'scheduled_at']);
        $wasApproved = $application->status === 'approved';
        $willBeApproved = $request->status === 'approved';

        if ($willBeApproved && ! $wasApproved) {
            $data['approved_at'] = now();
        }

        $application->update($data);

        if ($willBeApproved && ! $wasApproved) {
            $application->pet->update(['status' => 'adopted']);
        } elseif ($wasApproved && ! $willBeApproved) {
            $application->pet->update(['status' => 'available']);
        }

        return back()->with('success', 'Adoption application updated successfully.');
    }
}