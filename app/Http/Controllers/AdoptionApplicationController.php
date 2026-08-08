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

            // Send real-time FCM Push Notification to the approved adopter
            $petName = $application->pet->name ?? 'your pet';
            \App\Services\FirebaseNotificationService::sendToUser(
                $application->applicant_email,
                "🎉 Adoption Approved for {$petName}!",
                "Great news! Your adoption request for {$petName} was approved by CAWS staff! Check your notification bell for event details.",
                ['type' => 'adoption_status', 'status' => 'approved', 'pet_id' => $application->pet_id]
            );

            // Notify other pending applicants that the pet was adopted
            $otherApplicants = AdoptionApplication::where('pet_id', $application->pet_id)
                ->where('id', '!=', $application->id)
                ->whereIn('status', ['pending', 'under_review'])
                ->get();

            foreach ($otherApplicants as $otherApp) {
                \App\Services\FirebaseNotificationService::sendToUser(
                    $otherApp->applicant_email,
                    "🐾 {$petName} Has Found a Home!",
                    "The pet you requested ({$petName}) has found a forever home with another verified applicant. Browse other lovely pets available!",
                    ['type' => 'adoption_status', 'status' => 'adopted_by_other', 'pet_id' => $application->pet_id]
                );
            }

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