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
            'event_location' => ['nullable', 'string', 'max:255'],
            'event_notes' => ['nullable', 'string'],
        ]);

        $data = $request->only(['status', 'scheduled_at', 'event_location', 'event_notes']);
        $wasApproved = $application->status === 'approved';
        $willBeApproved = $request->status === 'approved';

        if ($willBeApproved && ! $wasApproved) {
            $data['approved_at'] = now();
        }

        $application->update($data);

        if ($willBeApproved && ! $wasApproved) {
            $petUpdate = ['status' => 'adopted'];
            if (empty($application->pet->name) && $application->message && preg_match('/Proposed Pet Name:\s*(.+)/i', $application->message, $matches)) {
                $petUpdate['name'] = trim($matches[1]);
            }
            $application->pet->update($petUpdate);

            AdoptionApplication::where('pet_id', $application->pet_id)
                ->where('id', '!=', $application->id)
                ->whereIn('status', ['pending', 'under_review'])
                ->update(['status' => 'rejected']);
        } elseif ($wasApproved && ! $willBeApproved) {
            $application->pet->update(['status' => 'available']);
        }

        return back()->with('success', 'Adoption application updated successfully.');
    }
}