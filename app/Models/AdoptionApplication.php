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
        'evaluator_id',
        'evaluator_name',
        'evaluation_recommendation',
        'evaluation_notes',
        'evaluated_at',
    ];

    protected $casts = [
        'scheduled_at'    => 'datetime',
        'signed_at'       => 'datetime',
        'staff_signed_at' => 'datetime',
        'evaluated_at'    => 'datetime',
    ];

    protected $appends = [
        'signature_url',
        'staff_signature_url',
        'valid_id_url',
        'barangay_certificate_url',
    ];

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
        $root = request() ? request()->getSchemeAndHttpHost() : config('app.url', 'http://localhost:8000');
        if ($this->staff_signature_path) {
            if (str_starts_with($this->staff_signature_path, 'http')) {
                return $this->staff_signature_path;
            }
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->staff_signature_path)) {
                return $root . '/storage/' . ltrim($this->staff_signature_path, '/');
            }
        }

        if ($this->staff && $this->staff->digital_signature_path) {
            return $this->staff->digital_signature_url;
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
}
