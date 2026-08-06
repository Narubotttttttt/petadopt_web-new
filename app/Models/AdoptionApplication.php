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
        'status',
        'scheduled_at',
        'event_location',
        'event_notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
}
