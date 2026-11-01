<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id',
        'date',
        'category',
        'vaccine_name',
        'administered_by',
        'is_external',
        'clinic_name',
        'proof_photo_path',
        'verification_status',
        'verified_by',
        'verified_at',
        'next_due_date',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'next_due_date' => 'date',
        'is_external' => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected $appends = [
        'proof_photo_url',
        'administered_role_label',
    ];

    public function getProofPhotoUrlAttribute(): ?string
    {
        if (!$this->proof_photo_path) {
            return null;
        }

        if (str_starts_with($this->proof_photo_path, 'http://') || str_starts_with($this->proof_photo_path, 'https://')) {
            return $this->proof_photo_path;
        }

        return asset('storage/' . ltrim($this->proof_photo_path, '/'));
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getAdministeredRoleLabelAttribute(): string
    {
        $clinician = $this->creator;
        if ((!$clinician || $clinician->name !== $this->administered_by) && !empty($this->administered_by)) {
            $matched = User::where('name', $this->administered_by)->first();
            if ($matched) {
                $clinician = $matched;
            }
        }

        if ($clinician?->role === 'admin' || stripos((string)$this->administered_by, 'admin') !== false) {
            return 'Admin';
        }

        if ($clinician?->role === 'staff') {
            return 'Authorized Staff';
        }

        if ($this->is_external) {
            return 'Licensed Veterinarian';
        }

        return 'Authorized Staff';
    }
}