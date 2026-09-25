<?php

namespace App\Http\Controllers;

use App\Models\AdoptionApplication;
use App\Services\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class AdoptionApplicationController extends Controller
{
    public function index(): View
    {
        AdminNotificationService::markAdoptionRequestsViewed();

        $applications = AdoptionApplication::with('pet')->latest()->paginate(10);

        return view('adoption-applications.index', compact('applications'));
    }

    public function show(AdoptionApplication $application): View
    {
        AdminNotificationService::markAdoptionRequestsViewed();

        $application->load(['pet.medicalLogs.creator', 'staff.staffProfile', 'evaluator']);

        $competingApplications = AdoptionApplication::where('pet_id', $application->pet_id)
            ->where('id', '!=', $application->id)
            ->orderByRaw("CASE WHEN status = 'approved' THEN 1 WHEN status IN ('pending', 'under_review') THEN 2 ELSE 3 END")
            ->orderByDesc('compatibility_score')
            ->latest('created_at')
            ->get();

        return view('adoption-applications.show', compact('application', 'competingApplications'));
    }

    public function markViewed(): JsonResponse
    {
        AdminNotificationService::markAdoptionRequestsViewed();

        return response()->json(['success' => true]);
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
                "Adoption Application Accepted for {$petName}!",
                "Great news! Your adoption application for {$petName} was accepted for final screening by CAWS! Please open the app to review your scheduled event details and bring your original physical ID and Barangay Certificate to finalize your adoption.",
                ['type' => 'adoption_status', 'status' => 'approved', 'pet_id' => $application->pet_id, 'requires_signature' => true]
            );

            $otherApplicants = AdoptionApplication::where('pet_id', $application->pet_id)
                ->where('id', '!=', $application->id)
                ->whereIn('status', ['pending', 'under_review'])
                ->get();

            foreach ($otherApplicants as $otherApp) {
                \App\Services\FirebaseNotificationService::sendToUser(
                    $otherApp->applicant_email,
                    "{$petName} Application Update - Priority Waitlist",
                    "Another applicant has been scheduled for final screening for {$petName}. Your application has been placed on our priority waitlist. If the pet becomes available, we will contact you immediately, or you can browse other lovely pets available!",
                    ['type' => 'adoption_status', 'status' => 'waitlisted', 'pet_id' => $application->pet_id]
                );
            }

            AdoptionApplication::where('pet_id', $application->pet_id)
                ->where('id', '!=', $application->id)
                ->whereIn('status', ['pending', 'under_review'])
                ->update(['status' => 'under_review']);
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
     * Physically verify adopter documents and finalize adoption handover with staff signature endorsement.
     */
    public function finalizeHandover(Request $request, AdoptionApplication $application): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'id_document_verified'   => ['accepted'],
            'barangay_cert_verified' => ['accepted'],
        ], [
            'id_document_verified.accepted'   => 'You must physically inspect and verify the applicant\'s original Valid ID.',
            'barangay_cert_verified.accepted' => 'You must physically inspect and verify the applicant\'s original Barangay Certificate of Residency.',
        ]);

        $signaturePath = null;
        if ($request->boolean('use_saved_signature')) {
            if (empty($user->digital_signature_path) || !\Illuminate\Support\Facades\Storage::disk('public')->exists($user->digital_signature_path)) {
                return back()->withErrors(['staff_signature' => 'No saved staff signature found on your profile. Please set your signature in Account Settings.']);
            }
            $signaturePath = $user->digital_signature_path;
        } else {
            $request->validate([
                'signature_data' => ['required', 'string'],
            ], [
                'signature_data.required' => 'Please provide an official staff signature to finalize the adoption handover.',
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
            $signaturePath = $fileName;

            // Auto-sync to staff profile if they don't have one or requested to save
            $staffProfile = $user->staffProfile ?: \App\Models\StaffProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'staff_code' => \App\Models\StaffProfile::generateStaffCode($user->role ?? 'staff'),
                    'full_name'  => $user->name,
                    'status'     => 'active',
                ]
            );
            if (empty($staffProfile->digital_signature_path) || $request->boolean('save_signature_to_profile')) {
                $staffProfile->digital_signature_path = $fileName;
                $staffProfile->save();
            }
        }

        $application->update([
            'staff_signature_path'   => $signaturePath,
            'staff_id'               => $user->id,
            'staff_name'             => $user->name,
            'staff_signed_at'        => now(),
            'documents_verified_at'  => now(),
            'documents_verified_by'  => $user->id,
            'id_document_verified'   => true,
            'barangay_cert_verified' => true,
            'status'                 => 'approved',
        ]);

        $application->pet?->update(['status' => 'adopted']);

        $petName = $application->pet?->name ?? 'your pet';
        \App\Services\FirebaseNotificationService::sendToUser(
            $application->applicant_email,
            "Adoption Finalized for {$petName}!",
            "Congratulations! Your physical documents have been verified and adoption of {$petName} is finalized. Your official Adoption Contract PDF is now unlocked and available in your app.",
            ['type' => 'adoption_finalized', 'status' => 'adopted', 'pet_id' => $application->pet_id, 'contract_unlocked' => true]
        );

        return back()->with('success', 'Physical documents verified and adoption handover finalized. Official contract unlocked.');
    }

    /**
     * Staff signature endorsement for an adoption contract.
     */
    public function signAsStaff(Request $request, AdoptionApplication $application): RedirectResponse
    {
        return $this->finalizeHandover($request, $application);
    }

    /**
     * Reset handover verification and staff signature endorsement (allows re-verifying or undoing).
     */
    public function resetHandover(AdoptionApplication $application): RedirectResponse
    {
        $application->update([
            'staff_signature_path'   => null,
            'staff_id'               => null,
            'staff_name'             => null,
            'staff_signed_at'        => null,
            'documents_verified_at'  => null,
            'documents_verified_by'  => null,
            'id_document_verified'   => false,
            'barangay_cert_verified' => false,
            'status'                 => 'approved',
        ]);

        if ($application->pet && $application->pet->status === 'adopted') {
            $application->pet->update(['status' => 'available']);
        }

        return back()->with('success', 'Handover verification and signature have been reset. You can now re-verify documents or sign again.');
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

        $currentUser = Auth::user();
        $isStaffOrAdmin = $currentUser && in_array($currentUser->role, ['admin', 'staff']);

        if (!$isStaffOrAdmin && !$application->is_finalized) {
            return response()->json([
                'error' => 'Official adoption contract is locked until CAWS staff physically inspects and verifies your original documents at the adoption meet-and-greet event.'
            ], 403);
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