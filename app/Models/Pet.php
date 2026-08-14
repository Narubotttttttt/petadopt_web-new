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
        'type',
        'age',
        'medical_history',
        'description',
        'photo_path',
        'status',
    ];

    protected $appends = [
        'photo_url',
        'primary_image_url',
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

    public function getPrimaryImageUrlAttribute(): ?string
    {
        return $this->photo_url;
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