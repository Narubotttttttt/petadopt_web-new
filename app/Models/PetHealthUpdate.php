<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PetHealthUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'adoption_application_id',
        'pet_id',
        'user_id',
        'photo_path',
        'health_status',
        'weight',
        'notes',
        'check_in_date',
        'status',
        'staff_remarks',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'weight' => 'float',
    ];

    protected $appends = [
        'photo_url',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        if (empty($this->photo_path)) {
            return null;
        }

        if (filter_var($this->photo_path, FILTER_VALIDATE_URL)) {
            return $this->photo_path;
        }

        return Storage::disk('public')->url($this->photo_path);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function application()
    {
        return $this->belongsTo(AdoptionApplication::class, 'adoption_application_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}