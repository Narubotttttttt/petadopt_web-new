<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdoptionApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'id_type',
        'message',
        'valid_id_path',
        'barangay_certificate_path',
        'signature_path',
        'signed_at',
        'staff_signature_path',
        'staff_id',
        'staff_name',
        'staff_signed_at',
        'status',
        'application_source',
        'compatibility_score',
        'scheduled_at',
        'event_location',
        'event_notes',
        'rejection_reason',
        'evaluator_id',
        'evaluator_name',
        'evaluation_recommendation',
        'evaluation_notes',
        'evaluated_at',
        'documents_verified_at',
        'documents_verified_by',
        'id_document_verified',
        'barangay_cert_verified',
    ];

    protected $casts = [
        'scheduled_at'           => 'datetime',
        'signed_at'              => 'datetime',
        'staff_signed_at'        => 'datetime',
        'evaluated_at'           => 'datetime',
        'documents_verified_at'  => 'datetime',
        'id_document_verified'   => 'boolean',
        'barangay_cert_verified' => 'boolean',
        'compatibility_score'    => 'float',
    ];

    protected $appends = [
        'signature_url',
        'staff_signature_url',
        'valid_id_url',
        'barangay_certificate_url',
        'is_finalized',
        'contract_unlocked',
        'is_archived_due_to_adoption',
        'display_status',
        'competing_active_count',
        'is_waitlisted_backup',
        'queue_position',
        'is_lead_candidate',
    ];

    public function getIsArchivedDueToAdoptionAttribute(): bool
    {
        if ($this->status !== 'rejected') {
            return false;
        }

        if (empty($this->rejection_reason)) {
            return (bool)($this->pet && $this->pet->status === 'adopted' && !$this->is_finalized);
        }

        $reason = strtolower($this->rejection_reason);

        return str_contains($reason, 'adopted by another') 
            || str_contains($reason, 'already adopted')
            || str_contains($reason, 'adopted to another')
            || str_contains($reason, 'found another home')
            || str_contains($reason, 'pet is no longer available');
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'rejected') {
            return $this->is_archived_due_to_adoption ? 'Archived' : 'Rejected';
        }

        if ($this->status === 'approved') {
            return $this->is_finalized ? 'Finalized' : 'Approved';
        }

        if ($this->is_waitlisted_backup) {
            return 'Waitlisted';
        }

        if ($this->status === 'under_review') {
            return 'Under Review';
        }

        return 'Pending';
    }

    public function getCompetingActiveCountAttribute(): int
    {
        if (!$this->pet) {
            return 1;
        }
        if ($this->pet->relationLoaded('adoptionApplications')) {
            return $this->pet->adoptionApplications
                ->whereIn('status', ['pending', 'under_review', 'approved'])
                ->count();
        }
        return AdoptionApplication::where('pet_id', $this->pet_id)
            ->whereIn('status', ['pending', 'under_review', 'approved'])
            ->count();
    }

    public function getScheduledCompetingApplicationAttribute(): ?AdoptionApplication
    {
        if (!$this->pet) {
            return null;
        }
        if ($this->pet->relationLoaded('adoptionApplications')) {
            return $this->pet->adoptionApplications
                ->where('id', '!=', $this->id)
                ->where('status', 'approved')
                ->whereNull('documents_verified_at')
                ->first();
        }
        return AdoptionApplication::where('pet_id', $this->pet_id)
            ->where('id', '!=', $this->id)
            ->where('status', 'approved')
            ->whereNull('documents_verified_at')
            ->first();
    }

    public function getIsWaitlistedBackupAttribute(): bool
    {
        return in_array($this->status, ['pending', 'under_review']) && $this->scheduled_competing_application !== null;
    }

    public function getQueuePositionAttribute(): int
    {
        if (!$this->pet) {
            return 1;
        }
        $activeApps = $this->pet->relationLoaded('adoptionApplications')
            ? $this->pet->adoptionApplications->whereIn('status', ['pending', 'under_review', 'approved'])
            : AdoptionApplication::where('pet_id', $this->pet_id)->whereIn('status', ['pending', 'under_review', 'approved'])->get();

        $sorted = $activeApps->sort(function ($a, $b) {
            $scoreA = $a->compatibility_score ?? 0;
            $scoreB = $b->compatibility_score ?? 0;
            if ($scoreA != $scoreB) {
                return $scoreB <=> $scoreA;
            }
            return $a->id <=> $b->id;
        })->values();

        $index = $sorted->search(fn ($app) => $app->id === $this->id);
        return $index !== false ? $index + 1 : 1;
    }

    public function getIsLeadCandidateAttribute(): bool
    {
        return $this->queue_position === 1;
    }

    public function getIsFinalizedAttribute(): bool
    {
        return !empty($this->staff_signature_path) && !empty($this->documents_verified_at);
    }

    public function getContractUnlockedAttribute(): bool
    {
        return $this->is_finalized;
    }

    public function getSignatureUrlAttribute(): ?string
    {
        if (!$this->signature_path) {
            return null;
        }
        if (str_starts_with($this->signature_path, 'http')) {
            return $this->signature_path;
        }
        $root = request() ? request()->getSchemeAndHttpHost() : config('app.url', 'http://localhost:8000');
        return $root . '/storage/' . ltrim($this->signature_path, '/');
    }

    public function getStaffSignatureUrlAttribute(): ?string
    {
        if (!$this->staff_signature_path) {
            return null;
        }
        if (str_starts_with($this->staff_signature_path, 'http')) {
            return $this->staff_signature_path;
        }
        $root = request() ? request()->getSchemeAndHttpHost() : config('app.url', 'http://localhost:8000');
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->staff_signature_path)) {
            return $root . '/storage/' . ltrim($this->staff_signature_path, '/');
        }
        return null;
    }

    public function getValidIdUrlAttribute(): ?string
    {
        if (!$this->valid_id_path) {
            return null;
        }
        if (str_starts_with($this->valid_id_path, 'http')) {
            return $this->valid_id_path;
        }
        $root = request() ? request()->getSchemeAndHttpHost() : config('app.url', 'http://localhost:8000');
        return $root . '/storage/' . ltrim($this->valid_id_path, '/');
    }

    public function getBarangayCertificateUrlAttribute(): ?string
    {
        if (!$this->barangay_certificate_path) {
            return null;
        }
        if (str_starts_with($this->barangay_certificate_path, 'http')) {
            return $this->barangay_certificate_path;
        }
        $root = request() ? request()->getSchemeAndHttpHost() : config('app.url', 'http://localhost:8000');
        return $root . '/storage/' . ltrim($this->barangay_certificate_path, '/');
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'applicant_email', 'email');
    }

    public function documentVerifier()
    {
        return $this->belongsTo(User::class, 'documents_verified_by');
    }
}
