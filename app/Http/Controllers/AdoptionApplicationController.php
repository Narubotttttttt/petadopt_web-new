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
        } elseif ($request->status === 'rejected' && $application->getOriginal('status') !== 'rejected') {
            $petName = $application->pet->name ?? 'your requested pet';
            \App\Services\FirebaseNotificationService::sendToUser(
                $application->applicant_email,
                "📋 Adoption Request Update — {$petName}",
                "Thank you for your interest in adopting {$petName}. Your application could not be approved at this time. Browse our other lovely pets waiting for a home!",
                ['type' => 'adoption_status', 'status' => 'rejected', 'pet_id' => $application->pet_id]
            );
        } elseif ($wasApproved && ! $willBeApproved) {
            $application->pet->update(['status' => 'available']);
        }

        return back()->with('success', 'Adoption application updated successfully.');
    }

    public function downloadContract($id)
    {
        $application = $id instanceof AdoptionApplication
            ? $id->load(['pet'])
            : AdoptionApplication::with(['pet'])->findOrFail($id);

        if (!in_array($application->status, ['approved', 'adopted'])) {
            return response()->json(['error' => 'Contract is only available for approved or adopted applications.'], 403);
        }

        $pet = $application->pet;

        if (!$pet) {
            return response()->json(['error' => 'Pet data not found for this application.'], 400);
        }

        // Build an adopter object from the application data
        $adopter = (object) [
            'name'         => $application->applicant_name ?? 'N/A',
            'phone_number' => $application->applicant_phone ?? 'N/A',
            'address'      => 'N/A',
        ];

        // Try to get richer data from the users table via email
        $userRecord = \App\Models\User::where('email', $application->applicant_email)->first();
        if ($userRecord) {
            if (empty($adopter->name) || $adopter->name === 'N/A') {
                $adopter->name = $userRecord->name ?? 'N/A';
            }
            if ($adopter->phone_number === 'N/A') {
                $adopter->phone_number = $userRecord->phone_number ?? 'N/A';
            }
            if (!empty($userRecord->address) && $userRecord->address !== 'N/A') {
                $adopter->address = $userRecord->address;
            }
        }

        // Extract address from application message if not already set
        if (($adopter->address === 'N/A' || empty($adopter->address)) && $application->message && preg_match('/Address:\s*(.+)/i', $application->message, $addrMatches)) {
            $adopter->address = trim($addrMatches[1]);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.adoption_contract', compact('application', 'adopter', 'pet'));

        $filename = 'Adoption_Contract_' . str_replace(' ', '_', $pet->name ?? 'Pet') . '.pdf';

        return $pdf->stream($filename);
    }
}