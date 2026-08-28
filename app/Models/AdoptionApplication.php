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
        'scheduled_at',
        'event_location',
        'event_notes',
    ];

    protected $casts = [
        'scheduled_at'    => 'datetime',
        'signed_at'       => 'datetime',
        'staff_signed_at' => 'datetime',
    ];

    protected $appends = [
        'signature_url',
        'staff_signature_url',
    ];

    public function getSignatureUrlAttribute(): ?string
    {
        if (!$this->signature_path) {
            return null;
        }
        if (str_starts_with($this->signature_path, 'http')) {
            return $this->signature_path;
        }
        return asset('storage/' . ltrim($this->signature_path, '/'));
    }

    public function getStaffSignatureUrlAttribute(): ?string
    {
        if (!$this->staff_signature_path) {
            return null;
        }
        if (str_starts_with($this->staff_signature_path, 'http')) {
            return $this->staff_signature_path;
        }
        return asset('storage/' . ltrim($this->staff_signature_path, '/'));
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
