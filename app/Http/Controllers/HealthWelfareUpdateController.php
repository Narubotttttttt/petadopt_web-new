<?php

namespace App\Http\Controllers;

use App\Models\AdoptersProfile;
use App\Models\AdoptionApplication;
use App\Models\MedicalLog;
use App\Models\PetHealthUpdate;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HealthWelfareUpdateController extends Controller
{
    public function index(Request $request): View
    {
        $tab              = $request->query('tab', $request->filled('verify_log_id') ? 'vet_proofs' : 'monthly_updates'); // 'monthly_updates', 'vet_proofs', or 'pet_dedications'
        $search           = trim((string) $request->query('search', ''));
        $status           = $request->query('status', 'all'); // all, healthy, minor_issue, under_treatment
        $species          = $request->query('species', 'all'); // all, dog, cat
        $period           = $request->query('period', 'all');  // all, this_month, last_month, last_3_months
        $vetStatus        = $request->query('vet_status', 'all'); // all, pending, verified, rejected
        $dedicationFilter = $request->query('dedication_filter', 'all'); // all, pending, cleared, neglect

        AdminNotificationService::markHealthWelfareUpdatesViewed();

        $autoVerifyLogPayload = null;
        if ($request->filled('verify_log_id')) {
            $tab = 'vet_proofs';
            $autoVerifyLog = MedicalLog::where('is_external', true)
                ->with(['pet', 'creator.adoptersProfile', 'verifier'])
                ->find($request->query('verify_log_id'));

            if ($autoVerifyLog) {
                $vPet = $autoVerifyLog->pet;
                $vPetName = $vPet?->name ?? ('Pet no. ' . $autoVerifyLog->pet_id);
                $vAdopter = $autoVerifyLog->creator?->adoptersProfile;
                $vAdopterName = $vAdopter?->full_name ?? ($autoVerifyLog->creator?->name ?? 'Adopter');

                $autoVerifyLogPayload = [
                    'id'                  => $autoVerifyLog->id,
                    'pet_id'              => $autoVerifyLog->pet_id,
                    'category'            => $autoVerifyLog->category,
                    'vaccine_name'        => $autoVerifyLog->vaccine_name,
                    'date'                => $autoVerifyLog->date ? (is_string($autoVerifyLog->date) ? substr($autoVerifyLog->date, 0, 10) : $autoVerifyLog->date->format('Y-m-d')) : null,
                    'clinic_name'         => $autoVerifyLog->clinic_name,
                    'administered_by'     => $autoVerifyLog->administered_by,
                    'next_due_date'       => $autoVerifyLog->next_due_date ? (is_string($autoVerifyLog->next_due_date) ? substr($autoVerifyLog->next_due_date, 0, 10) : $autoVerifyLog->next_due_date->format('Y-m-d')) : null,
                    'proof_photo_url'     => $autoVerifyLog->proof_photo_url,
                    'verification_status' => $autoVerifyLog->verification_status,
                    'pet' => [
                        'id'         => $vPet?->id,
                        'name'       => $vPetName,
                        'type'       => $vPet?->type,
                        'breed'      => $vPet?->breed,
                        'photo_url'  => $vPet?->photo_url,
                        'photo_path' => $vPet?->photo_path,
                    ],
                    'creator' => [
                        'name'             => $vAdopterName,
                        'email'            => $autoVerifyLog->creator?->email,
                        'adopters_profile' => [
                            'phone' => $vAdopter?->phone,
                        ],
                    ],
                ];
            }
        }

        // Routine Monthly Pet Updates Query (Strictly living pets)
        $query = PetHealthUpdate::where('health_status', '!=', 'deceased')->with([
            'pet',
            'user.adoptersProfile',
            'application',
            'reviewer'
        ]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('pet', function ($petQ) use ($search) {
                    $petQ->where('name', 'like', "%{$search}%")
                        ->orWhere('breed', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                })->orWhereHas('user', function ($userQ) use ($search) {
                    $userQ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('user.adoptersProfile', function ($profQ) use ($search) {
                    $profQ->where('adopter_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('health_status', $status);
        }

        if ($species !== 'all') {
            $query->whereHas('pet', function ($petQ) use ($species) {
                $petQ->where('type', $species);
            });
        }

        if ($period === 'this_month') {
            $query->whereBetween('check_in_date', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period === 'last_month') {
            $query->whereBetween('check_in_date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
        } elseif ($period === 'last_3_months') {
            $query->where('check_in_date', '>=', now()->subMonths(3)->startOfDay());
        }

        $reports = $query->latest('check_in_date')->latest('created_at')->paginate(12)->withQueryString();

        // Veterinary External Proofs Submissions
        $vetQuery = MedicalLog::where('is_external', true)->with([
            'pet',
            'creator.adoptersProfile',
            'verifier'
        ]);

        if (!empty($search)) {
            $vetQuery->where(function ($q) use ($search) {
                $q->where('vaccine_name', 'like', "%{$search}%")
                    ->orWhere('clinic_name', 'like', "%{$search}%")
                    ->orWhere('administered_by', 'like', "%{$search}%")
                    ->orWhereHas('pet', function ($petQ) use ($search) {
                        $petQ->where('name', 'like', "%{$search}%")
                            ->orWhere('breed', 'like', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($userQ) use ($search) {
                        $userQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($vetStatus !== 'all') {
            $vetQuery->where('verification_status', $vetStatus);
        }

        $vetLogs = $vetQuery->latest('created_at')->paginate(12, ['*'], 'vet_page')->withQueryString();

        // Pet Dedications & Memorials Submissions
        $dedicationQuery = PetHealthUpdate::where('health_status', 'deceased')->with([
            'pet.medicalLogs' => function ($q) {
                $q->latest('date')->latest('created_at');
            },
            'user.adoptersProfile',
            'application',
            'reviewer'
        ]);

        if (!empty($search)) {
            $dedicationQuery->where(function ($q) use ($search) {
                $q->whereHas('pet', function ($petQ) use ($search) {
                    $petQ->where('name', 'like', "%{$search}%")
                        ->orWhere('breed', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                })->orWhereHas('user', function ($userQ) use ($search) {
                    $userQ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('user.adoptersProfile', function ($profQ) use ($search) {
                    $profQ->where('adopter_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('staff_remarks', 'like', "%{$search}%");
            });
        }

        if ($dedicationFilter === 'pending') {
            $dedicationQuery->where(function($q) {
                $q->whereNull('review_decision')->orWhere('review_decision', 'pending');
            });
        } elseif ($dedicationFilter === 'investigating') {
            $dedicationQuery->where('review_decision', 'investigation_pending');
        } elseif ($dedicationFilter === 'cleared') {
            $dedicationQuery->where('review_decision', 'cleared_accidental');
        } elseif ($dedicationFilter === 'neglect') {
            $dedicationQuery->where('review_decision', 'neglect_confirmed');
        }

        $dedications = $dedicationQuery->latest('created_at')->paginate(12, ['*'], 'dedication_page')->withQueryString();

        // Vet Proofs KPI counts
        $totalVetLogsCount     = MedicalLog::where('is_external', true)->count();
        $pendingVetLogsCount   = MedicalLog::where('is_external', true)->where('verification_status', 'pending')->count();
        $verifiedVetLogsCount  = MedicalLog::where('is_external', true)->where('verification_status', 'verified')->count();
        $rejectedVetLogsCount  = MedicalLog::where('is_external', true)->where('verification_status', 'rejected')->count();

        // Monthly Updates KPI statistics (living pets)
        $totalReports     = PetHealthUpdate::where('health_status', '!=', 'deceased')->count();
        $thisMonthReports = PetHealthUpdate::where('health_status', '!=', 'deceased')->whereBetween('check_in_date', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $healthyReports   = PetHealthUpdate::where('health_status', 'healthy')->count();
        $attentionReports = PetHealthUpdate::whereIn('health_status', ['minor_issue', 'under_treatment'])->count();

        // Dedications & Memorials KPI statistics
        $totalDedicationsCount      = PetHealthUpdate::where('health_status', 'deceased')->count();
        $pendingDeceasedCount       = PetHealthUpdate::where('health_status', 'deceased')->where(function($q) {
            $q->whereNull('review_decision')->orWhere('review_decision', 'pending');
        })->count();
        $investigatingDeceasedCount = PetHealthUpdate::where('health_status', 'deceased')->where('review_decision', 'investigation_pending')->count();
        $clearedDeceasedCount       = PetHealthUpdate::where('health_status', 'deceased')->where('review_decision', 'cleared_accidental')->count();
        $neglectDeceasedCount       = PetHealthUpdate::where('health_status', 'deceased')->where('review_decision', 'neglect_confirmed')->count();

        $totalApprovedAdopters = AdoptionApplication::where('status', 'approved')->distinct('applicant_email')->count();

        $viewName = view()->exists('health-welfare-updates.index') ? 'health-welfare-updates.index' : 'adopters.reports';

        return view($viewName, compact(
            'tab',
            'reports',
            'vetLogs',
            'dedications',
            'search',
            'status',
            'species',
            'period',
            'vetStatus',
            'dedicationFilter',
            'totalVetLogsCount',
            'pendingVetLogsCount',
            'verifiedVetLogsCount',
            'rejectedVetLogsCount',
            'totalReports',
            'thisMonthReports',
            'healthyReports',
            'attentionReports',
            'totalDedicationsCount',
            'pendingDeceasedCount',
            'investigatingDeceasedCount',
            'clearedDeceasedCount',
            'neglectDeceasedCount',
            'totalApprovedAdopters',
            'autoVerifyLogPayload'
        ));
    }

    public function reviewDeceasedReport(Request $request, int $id): RedirectResponse
    {
        $report = PetHealthUpdate::where('health_status', 'deceased')->findOrFail($id);

        $validated = $request->validate([
            'decision'      => ['required', 'in:cleared_accidental,neglect_confirmed,investigation_pending'],
            'staff_remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $pet = $report->pet;
        $petName = $pet?->name ?? 'the pet';
        $adopterEmail = $report->user?->email ?: ($report->application?->applicant_email ?? null);

        $newStatus = match ($validated['decision']) {
            'cleared_accidental'    => 'memorial_cleared',
            'neglect_confirmed'     => 'memorial_neglect',
            'investigation_pending' => 'memorial_investigating',
            default                 => 'memorial_recorded',
        };

        $report->update([
            'status'          => $newStatus,
            'review_decision' => $validated['decision'],
            'staff_remarks'   => $validated['staff_remarks'] ?? null,
            'reviewed_by'     => Auth::id(),
            'reviewed_at'     => now(),
        ]);

        \App\Services\AdminNotificationService::markAsRead('dedication_' . $report->id);

        if ($validated['decision'] === 'investigation_pending') {
            $profile = null;
            if ($report->user?->adoptersProfile) {
                $profile = $report->user->adoptersProfile;
            } elseif ($adopterEmail) {
                $profile = AdoptersProfile::where('email', $adopterEmail)->first();
            }

            if ($profile) {
                $existingNotes = $profile->admin_notes ? $profile->admin_notes . ' | ' : '';
                $remarksText = !empty($validated['staff_remarks']) ? ('. Notes: ' . $validated['staff_remarks']) : '';
                $profile->update([
                    'admin_notes' => $existingNotes . 'On-Site Verification Requested (' . now()->format('M d, Y') . '): Shelter requested an in-person meeting/home visit regarding companion ' . $petName . $remarksText,
                ]);
            }

            if ($adopterEmail) {
                $visitMessage = "CAWS Shelter has requested an on-site consultation/home visit regarding the passing of {$petName}. Our team will contact you to coordinate the schedule.";
                if (!empty($validated['staff_remarks'])) {
                    $visitMessage .= " Shelter remarks: {$validated['staff_remarks']}";
                }

                \App\Services\FirebaseNotificationService::sendToUser(
                    $adopterEmail,
                    'On-Site Verification Requested - CAWS Shelter',
                    $visitMessage,
                    [
                        'type'           => 'pet_deceased_investigation',
                        'pet_id'         => (string) $report->pet_id,
                        'application_id' => (string) $report->adoption_application_id,
                    ]
                );
            }

            return back()->with('success', "Deceased report for {$petName} placed on hold for on-site inquiry. Adopter has been notified.");
        }

        if ($validated['decision'] === 'neglect_confirmed') {
            $profile = null;
            if ($report->user?->adoptersProfile) {
                $profile = $report->user->adoptersProfile;
            } elseif ($adopterEmail) {
                $profile = AdoptersProfile::where('email', $adopterEmail)->first();
            }

            if ($profile) {
                $existingNotes = $profile->admin_notes ? $profile->admin_notes . ' | ' : '';
                $remarksText = !empty($validated['staff_remarks']) ? ('. Remarks: ' . $validated['staff_remarks']) : '';
                $profile->update([
                    'status'      => 'restricted',
                    'admin_notes' => $existingNotes . 'Administrative Investigation (' . now()->format('M d, Y') . '): Neglect confirmed in passing of pet ' . $petName . $remarksText,
                ]);
            }

            if ($adopterEmail) {
                $neglectMessage = "CAWS did not find your reported reason for {$petName}'s passing reasonable. As a result, your account has been restricted and you are not allowed to adopt another pet from our shelter.";
                if (!empty($validated['staff_remarks'])) {
                    $neglectMessage .= " Shelter note: {$validated['staff_remarks']}";
                }

                \App\Services\FirebaseNotificationService::sendToUser(
                    $adopterEmail,
                    'Account Restricted - CAWS Shelter',
                    $neglectMessage,
                    [
                        'type'           => 'pet_deceased_neglect',
                        'pet_id'         => (string) $report->pet_id,
                        'application_id' => (string) $report->adoption_application_id,
                    ]
                );
            }

            return back()->with('success', "Deceased report for {$petName} reviewed: Neglect confirmed. Adopter account has been restricted from future shelter adoptions.");
        }

        if ($adopterEmail) {
            \App\Services\FirebaseNotificationService::sendToUser(
                $adopterEmail,
                'With Deep Sympathy from CAWS',
                "We are deeply saddened to hear about the loss of {$petName}. Thank you for providing them a loving home. Your memorial record has been officially verified by CAWS shelter staff.",
                [
                    'type'           => 'pet_deceased_confirmed',
                    'pet_id'         => (string) $report->pet_id,
                    'application_id' => (string) $report->adoption_application_id,
                ]
            );
        }

        return back()->with('success', "Deceased report for {$petName} verified as accidental / natural passing. Adopter remains in good standing, and 1 adoption slot has been freed up under household limit.");
    }

    public function markViewed(): JsonResponse
    {
        AdminNotificationService::markHealthWelfareUpdatesViewed();
        return response()->json(['success' => true]);
    }
}
