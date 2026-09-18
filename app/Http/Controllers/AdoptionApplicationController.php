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
        $application->load(['pet.medicalLogs.creator', 'staff.staffProfile', 'evaluator']);

        return view('adoption-applications.show', compact('application'));
    }

    public function update(Request $request, AdoptionApplication $application): RedirectResponse
    {
        $currentUser = Auth::user();

        $request->validate([
            'status'           => ['required', Rule::in(['approved', 'rejected'])],
            'scheduled_at'     => ['nullable', 'date'],
            'event_location'   => ['nullable', 'required_if:status,approved', 'string', 'max:255'],
            'event_notes'      => ['nullable', 'required_if:status,approved', 'string'],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:2000'],
        ]);

        $data = $request->only(['status', 'scheduled_at', 'event_location', 'event_notes']);

        if ($request->filled('rejection_reason')) {
            $data['rejection_reason'] = $request->rejection_reason;
        }

        $data['evaluator_id']   = $currentUser->id;
        $data['evaluator_name'] = $currentUser->name;
        $data['evaluated_at']   = now();

        $wasApproved    = $application->status === 'approved';
        $willBeApproved = $request->status === 'approved';
        $wasRejected    = $application->status === 'rejected';
        $willBeRejected = $request->status === 'rejected';

        if ($willBeApproved && ! $wasApproved) {
            $data['approved_at'] = now();

            if (!empty($currentUser->digital_signature_path) && empty($application->staff_signature_path)) {
                $data['staff_signature_path'] = $currentUser->digital_signature_path;
                $data['staff_id']             = $currentUser->id;
                $data['staff_name']           = $currentUser->name;
                $data['staff_signed_at']      = now();
            }
        }

        $application->update($data);

        if ($wasApproved && ! $willBeApproved) {
            $application->pet?->update(['status' => 'available']);
        }

        if ($willBeApproved && ! $wasApproved) {
            $petUpdate = ['status' => 'adopted'];
            if (empty($application->pet->name) && $application->message && preg_match('/Proposed Pet Name:\s*(.+)/i', $application->message, $matches)) {
                $petUpdate['name'] = trim($matches[1]);
            }
            $application->pet?->update($petUpdate);

            if (!empty($application->applicant_email)) {
                $user = \App\Models\User::where('email', $application->applicant_email)->first();
                $adopterCode = $user ? sprintf('ADP-%04d', $user->id) : sprintf('ADP-%04d', $application->id + 100);
                \App\Models\AdoptersProfile::firstOrCreate(
                    ['email' => $application->applicant_email],
                    [
                        'user_id'      => $user?->id,
                        'adopter_code' => $adopterCode,
                        'full_name'    => $application->applicant_name,
                        'phone'        => $application->applicant_phone,
                        'address'      => $application->address,
                        'status'       => 'active',
                    ]
                );
            }

            $petName = $application->pet?->name ?? 'your pet';
            \App\Services\FirebaseNotificationService::sendToUser(
                $application->applicant_email,
                "Adoption Approved for {$petName}!",
                "Great news! Your adoption request for {$petName} was approved by CAWS! Please open the app to review and digitally sign your adoption contract to finalize the pickup.",
                ['type' => 'adoption_status', 'status' => 'approved', 'pet_id' => $application->pet_id, 'requires_signature' => true]
            );

            $otherApplicants = AdoptionApplication::where('pet_id', $application->pet_id)
                ->where('id', '!=', $application->id)
                ->whereIn('status', ['pending', 'under_review'])
                ->get();

            foreach ($otherApplicants as $otherApp) {
                \App\Services\FirebaseNotificationService::sendToUser(
                    $otherApp->applicant_email,
                    "{$petName} Has Found a Home!",
                    "The pet you requested ({$petName}) has found a forever home with another verified applicant. Browse other lovely pets available!",
                    ['type' => 'adoption_status', 'status' => 'adopted_by_other', 'pet_id' => $application->pet_id]
                );
            }

            AdoptionApplication::where('pet_id', $application->pet_id)
                ->where('id', '!=', $application->id)
                ->whereIn('status', ['pending', 'under_review'])
                ->update(['status' => 'rejected']);
        } elseif ($willBeRejected && ! $wasRejected) {
            $petName = $application->pet?->name ?? 'your requested pet';
            $reason = !empty($application->rejection_reason)
                ? $application->rejection_reason
                : (!empty($application->evaluation_notes)
                    ? $application->evaluation_notes
                    : 'Your application could not be approved based on shelter adoption requirements.');

            $body = "Your adoption request for {$petName} was not approved. Reason: {$reason}";

            \App\Services\FirebaseNotificationService::sendToUser(
                $application->applicant_email,
                "Adoption Request Update - {$petName}",
                $body,
                [
                    'type'             => 'adoption_status',
                    'status'           => 'rejected',
                    'pet_id'           => (string) $application->pet_id,
                    'rejection_reason' => $reason,
                ]
            );
        }

        return back()->with('success', 'Adoption application updated successfully.');
    }

    /**
     * Staff signature endorsement for an adoption contract.
     */
    public function signAsStaff(Request $request, AdoptionApplication $application): RedirectResponse
    {
        $user = Auth::user();

        if ($request->boolean('use_saved_signature')) {
            if (empty($user->digital_signature_path) || !\Illuminate\Support\Facades\Storage::disk('public')->exists($user->digital_signature_path)) {
                return back()->withErrors(['staff_signature' => 'No saved staff signature found on your profile. Please set your signature in Account Settings.']);
            }

            $application->update([
                'staff_signature_path' => $user->digital_signature_path,
                'staff_id'             => $user->id,
                'staff_name'           => $user->name,
                'staff_signed_at'      => now(),
            ]);

            return back()->with('success', 'Official CAWS staff signature attached successfully.');
        }

        $request->validate([
            'signature_data' => ['required', 'string'],
        ]);

        $sigData = $request->signature_data;
        if (preg_match('/^data:image\/(\w+);base64,/', $sigData, $type)) {
            $sigData = substr($sigData, strpos($sigData, ',') + 1);
        }
        $decoded = base64_decode($sigData);
        if (!$decoded) {
            return back()->withErrors(['staff_signature' => 'Invalid signature image data.']);
        }

        $fileName = 'signatures/staff_app_' . $application->id . '_' . time() . '.png';
        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $decoded);

        $application->update([
            'staff_signature_path' => $fileName,
            'staff_id'             => $user->id,
            'staff_name'           => $user->name,
            'staff_signed_at'      => now(),
        ]);

        // Auto-sync to staff profile if they don't have one
        $staffProfile = $user->staffProfile ?: \App\Models\StaffProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'staff_code' => sprintf('STF-%04d', $user->id),
                'full_name'  => $user->name,
                'status'     => 'active',
            ]
        );
        if (empty($staffProfile->digital_signature_path)) {
            $staffProfile->digital_signature_path = $fileName;
            $staffProfile->save();
        }

        return back()->with('success', 'Official CAWS staff signature attached successfully.');
    }

    public function downloadContract($id)
    {
        $application = $id instanceof AdoptionApplication
            ? $id->load(['pet', 'staff'])
            : AdoptionApplication::with(['pet', 'staff'])->findOrFail($id);

        if (!in_array($application->status, ['approved', 'adopted'])) {
            return response()->json(['error' => 'Contract is only available for approved or adopted applications.'], 403);
        }

        if (empty($application->signature_path)) {
            return response()->json([
                'error' => 'The adoption contract must be digitally signed by the adopter before downloading or printing.'
            ], 422);
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

        // 1. Prepare Adopter Digital Signature
        $signatureBase64 = null;
        if (!empty($application->signature_path)) {
            $sigFullPath = storage_path('app/public/' . ltrim($application->signature_path, '/'));
            if (file_exists($sigFullPath)) {
                $sigData = file_get_contents($sigFullPath);
                $signatureBase64 = 'data:image/png;base64,' . base64_encode($sigData);
            }
        }

        // 2. Prepare Staff Digital Signature & Name
        $staffSignatureBase64 = null;
        $staffPath = $application->staff_signature_path;

        // Fall back to staff profile signature if application path is not explicitly set
        if (empty($staffPath) && $application->staff && !empty($application->staff->digital_signature_path)) {
            $staffPath = $application->staff->digital_signature_path;
        }

        if (!empty($staffPath)) {
            $staffFullPath = storage_path('app/public/' . ltrim($staffPath, '/'));
            if (file_exists($staffFullPath)) {
                $staffSigData = file_get_contents($staffFullPath);
                $staffSignatureBase64 = 'data:image/png;base64,' . base64_encode($staffSigData);
            }
        }

        $staffSigner = $application->staff ?: ($application->staff_id ? \App\Models\User::find($application->staff_id) : null);
        $staffName = $application->staff_name ?: ($staffSigner?->name ?? 'CDO Animal Welfare Society Inc.');
        $staffTitle = ($staffSigner && $staffSigner->role === 'admin')
            ? 'Shelter Administrator'
            : ($staffSigner?->staffProfile?->position_title ?? 'CAWS Authorized Representative');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.adoption_contract', compact(
            'application',
            'adopter',
            'pet',
            'signatureBase64',
            'staffSignatureBase64',
            'staffName',
            'staffTitle'
        ));

        $filename = 'Adoption_Contract_' . str_replace(' ', '_', $pet->name ?? 'Pet') . '.pdf';

        return $pdf->stream($filename);
    }
}