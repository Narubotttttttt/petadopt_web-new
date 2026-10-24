<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'breed',
        'color',
        'gender',
        'size',
        'type',
        'age',
        'medical_history',
        'description',
        'photo_path',
        'status',
        'rescue_date',
        'rescue_location',
        'intake_condition',
        'added_by_user_id',
        'added_by_name',
    ];

    protected $casts = [
        'rescue_date' => 'date',
    ];

    protected $appends = [
        'photo_url',
        'primary_image_url',
        'days_in_shelter',
        'days_in_shelter_formatted',
    ];

    public function getDaysInShelterAttribute(): int
    {
        $startDate = $this->rescue_date ?? $this->created_at;
        if (! $startDate) {
            return 0;
        }

        return max(0, (int) $startDate->startOfDay()->diffInDays(now()->startOfDay()));
    }

    public function getDaysInShelterFormattedAttribute(): string
    {
        if ($this->status === 'adopted') {
            return 'Adopted';
        }

        $days = $this->days_in_shelter;

        if ($days === 0) {
            return 'Registered today';
        }

        return $days . ' ' . ($days === 1 ? 'day' : 'days') . ' in shelter';
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (empty($this->photo_path)) {
            return null;
        }

        if (filter_var($this->photo_path, FILTER_VALIDATE_URL)) {
            return $this->photo_path;
        }

        $root = request() ? request()->getSchemeAndHttpHost() : config('app.url', 'http://localhost:8000');
        return $root . '/storage/' . ltrim($this->photo_path, '/');
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        return $this->photo_url;
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    public function adoptionApplications()
    {
        return $this->hasMany(AdoptionApplication::class);
    }

    public function temperamentTags()
    {
        return $this->belongsToMany(TemperamentTag::class, 'pet_temperament_tag');
    }

    public function healthUpdates()
    {
        return $this->hasMany(PetHealthUpdate::class);
    }

    public function medicalLogs()
    {
        return $this->hasMany(MedicalLog::class)->orderByDesc('date');
    }
}