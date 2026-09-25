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
    public function index(Request $request): View
    {
        AdminNotificationService::markAdoptionRequestsViewed();

        $activeTab = $request->query('status', 'all');
        $search = trim($request->query('search', ''));

        // Base query with relations
        $baseQuery = AdoptionApplication::with([
            'pet.adoptionApplications' => function ($q) {
                $q->whereIn('status', ['pending', 'under_review', 'approved'])
                  ->select('id', 'pet_id', 'applicant_name', 'status', 'compatibility_score', 'created_at', 'documents_verified_at', 'staff_signature_path');
            }
        ]);

        // Apply search if provided
        if (!empty($search)) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('applicant_name', 'like', "%{$search}%")
                  ->orWhere('applicant_email', 'like', "%{$search}%")
                  ->orWhere('applicant_phone', 'like', "%{$search}%")
                  ->orWhereHas('pet', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('breed', 'like', "%{$search}%");
                  });
            });
        }

        // Live counts for status tabs (respecting search query)
        $countQuery = clone $baseQuery;
        $countAll = (clone $countQuery)->count();
        $countPending = (clone $countQuery)->whereIn('status', ['pending', 'under_review'])->count();
        $countScheduled = (clone $countQuery)->where('status', 'approved')->whereNull('documents_verified_at')->count();
        $countFinalized = (clone $countQuery)->whereNotNull('documents_verified_at')->count();
        $countRejected = (clone $countQuery)->where('status', 'rejected')->count();

        // Apply status filter based on active tab
        $query = clone $baseQuery;
        switch ($activeTab) {
            case 'pending':
                $query->whereIn('status', ['pending', 'under_review']);
                break;
            case 'scheduled':
                $query->where('status', 'approved')->whereNull('documents_verified_at');
                break;
            case 'finalized':
                $query->whereNotNull('documents_verified_at');
                break;
            case 'rejected':
                $query->where('status', 'rejected');
                break;
            case 'all':
            default:
                $activeTab = 'all';
                break;
        }

        $applications = $query->latest()->paginate(10)->withQueryString();

        // Count pending requests submitted today
        $countTodayPending = (clone $countQuery)
            ->whereIn('status', ['pending', 'under_review'])
            ->whereDate('created_at', today())
            ->count();

        // Track when pending requests were viewed
        $latestPendingId = (clone $baseQuery)
            ->whereIn('status', ['pending', 'under_review'])
            ->max('id') ?? 0;

        $userId = auth()->id();
        $cacheKey = 'last_viewed_pending_id_' . ($userId ?? 'guest');

        if ($activeTab === 'pending') {
            session(['last_viewed_pending_id' => $latestPendingId]);
            if ($userId) {
                \Illuminate\Support\Facades\Cache::put($cacheKey, $latestPendingId, now()->addDays(30));
            }
        }

        $lastViewedPendingId = session('last_viewed_pending_id') ?? ($userId ? \Illuminate\Support\Facades\Cache::get($cacheKey, null) : null);

        // If never viewed before in this session/cache, treat pending applications as unseen
        $hasUnseenPending = ($countPending > 0) && ($activeTab !== 'pending') && ($lastViewedPendingId === null || $latestPendingId > $lastViewedPendingId);

        // Compute badge count and label for new pending requests
        $newBadgeCount = $countTodayPending > 0 ? $countTodayPending : ($hasUnseenPending ? $countPending : 0);

        $counts = [
            'all'           => $countAll,
            'pending'       => $countPending,
            'today_pending' => $countTodayPending,
            'scheduled'     => $countScheduled,
            'finalized'     => $countFinalized,
            'rejected'      => $countRejected,
        ];

        $latestId = AdoptionApplication::max('id') ?? 0;

        return view('adoption-applications.index', compact('applications', 'activeTab', 'search', 'counts', 'hasUnseenPending', 'newBadgeCount', 'latestId'));
    }

    /**
     * Lightweight heartbeat endpoint for real-time application updates.
     */
    public function realtimeCheck(Request $request): JsonResponse
    {
        $clientLatestId = (int) $request->query('latest_id', 0);

        // Fetch live counts
        $countAll = AdoptionApplication::count();
        $countPending = AdoptionApplication::whereIn('status', ['pending', 'under_review'])->count();
        $countTodayPending = AdoptionApplication::whereIn('status', ['pending', 'under_review'])
            ->whereDate('created_at', today())
            ->count();
        $countScheduled = AdoptionApplication::where('status', 'approved')->whereNull('documents_verified_at')->count();
        $countFinalized = AdoptionApplication::whereNotNull('documents_verified_at')->count();
        $countRejected = AdoptionApplication::where('status', 'rejected')->count();

        $latestPendingId = AdoptionApplication::whereIn('status', ['pending', 'under_review'])->max('id') ?? 0;
        $globalLatestId = AdoptionApplication::max('id') ?? 0;

        $hasNew = ($clientLatestId > 0 && $globalLatestId > $clientLatestId);

        // If new applications arrived since client last checked, fetch preview data for toast
        $newApplications = [];
        if ($hasNew) {
            $newApplications = AdoptionApplication::with('pet')
                ->where('id', '>', $clientLatestId)
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($app) {
                    return [
                        'id' => $app->id,
                        'applicant_name' => $app->applicant_name,
                        'pet_name' => $app->pet?->name ?? 'Pet #' . $app->pet_id,
                        'status' => $app->status,
                        'created_at_human' => $app->created_at ? $app->created_at->diffForHumans() : 'Just now',
                    ];
                });
        }

        $userId = auth()->id();
        $cacheKey = 'last_viewed_pending_id_' . ($userId ?? 'guest');
        $lastViewedPendingId = session('last_viewed_pending_id') ?? ($userId ? \Illuminate\Support\Facades\Cache::get($cacheKey, null) : null);
        $hasUnseenPending = ($countPending > 0) && ($lastViewedPendingId === null || $latestPendingId > $lastViewedPendingId);
        $newBadgeCount = $countTodayPending > 0 ? $countTodayPending : ($hasUnseenPending ? $countPending : 0);

        return response()->json([
            'success' => true,
            'latest_id' => $globalLatestId,
            'latest_pending_id' => $latestPendingId,
            'has_new' => $hasNew,
            'has_unseen_pending' => $hasUnseenPending,
            'new_badge_count' => $newBadgeCount,
            'new_count' => count($newApplications),
            'new_applications' => $newApplications,
            'counts' => [
                'all'           => $countAll,
                'pending'       => $countPending,
                'today_pending' => $countTodayPending,
                'scheduled'     => $countScheduled,
                'finalized'     => $countFinalized,
                'rejected'      => $countRejected,
            ],
        ]);
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

    public function markViewed(?Request $request = null): JsonResponse
    {
        AdminNotificationService::markAdoptionRequestsViewed();

        $latestPendingId = AdoptionApplication::whereIn('status', ['pending', 'under_review'])->max('id') ?? 0;
        session(['last_viewed_pending_id' => $latestPendingId]);

        $userId = auth()->id();
        if ($userId) {
            $cacheKey = 'last_viewed_pending_id_' . $userId;
            \Illuminate\Support\Facades\Cache::put($cacheKey, $latestPendingId, now()->addDays(30));
        }

        return response()->json([
            'success' => true,
            'last_viewed_pending_id' => $latestPendingId,
        ]);
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
            $petName = $application->pet?->name ?? 'This pet';

            // Check if this pet is already adopted or finalized by another application
            $finalizedOther = AdoptionApplication::where('pet_id', $application->pet_id)
                ->where('id', '!=', $application->id)
                ->where(function ($q) {
                    $q->whereNotNull('documents_verified_at')
                      ->orWhereNotNull('staff_signature_path');
                })
                ->first();

            if ($finalizedOther || ($application->pet && $application->pet->status === 'adopted')) {
                $adopterName = $finalizedOther ? $finalizedOther->applicant_name : 'another applicant';
                return back()->withErrors([
                    'status' => "Cannot approve application. {$petName} has already been adopted by {$adopterName}."
                ])->withInput();
            }

            // Check if another applicant is currently scheduled/approved for this pet
            $scheduledOther = AdoptionApplication::where('pet_id', $application->pet_id)
                ->where('id', '!=', $application->id)
                ->where('status', 'approved')
                ->first();

            if ($scheduledOther) {
                return back()->withErrors([
                    'status' => "Cannot approve application. Another applicant ({$scheduledOther->applicant_name}) is already approved and scheduled for screening for {$petName}. Only one candidate can be scheduled at a time."
                ])->withInput();
            }

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

        // Auto-reject any other competing applications for this pet
        $competingApps = AdoptionApplication::where('pet_id', $application->pet_id)
            ->where('id', '!=', $application->id)
            ->where('status', '!=', 'rejected')
            ->get();

        foreach ($competingApps as $compApp) {
            $compApp->update([
                'status'           => 'rejected',
                'rejection_reason' => "This pet has officially been adopted by another applicant. Thank you for your support, and we encourage you to browse other available pets!",
                'evaluator_id'     => $user->id,
                'evaluator_name'   => $user->name,
                'evaluated_at'     => now(),
            ]);

            \App\Services\FirebaseNotificationService::sendToUser(
                $compApp->applicant_email,
                "Adoption Application Update - {$petName}",
                "Thank you for your love and interest in {$petName}. {$petName} has officially been adopted into a loving home. We invite you to explore other wonderful pets waiting for adoption at CAWS!",
                [
                    'type'             => 'adoption_status',
                    'status'           => 'rejected',
                    'pet_id'           => (string) $application->pet_id,
                    'rejection_reason' => 'Pet already adopted by another applicant',
                ]
            );
        }

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

    /**
     * Delete an adoption application (e.g. test or spam).
     */
    public function destroy(AdoptionApplication $application): RedirectResponse
    {
        $applicantName = $application->applicant_name;
        $petName = $application->pet?->name ?? 'Pet';

        // Clean up digital signature file from storage if present
        if ($application->signature_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($application->signature_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($application->signature_path);
        }

        // If deleting an approved application for an unfinalized pet, restore pet status to available
        if ($application->status === 'approved' && $application->pet && $application->pet->status === 'adopted' && !$application->is_finalized) {
            $application->pet->update(['status' => 'available']);
        }

        $application->delete();

        return back()->with('success', "Adoption request from {$applicantName} for {$petName} has been deleted.");
    }
}