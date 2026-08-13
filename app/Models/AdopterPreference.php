<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdopterPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preferred_species',
        'preferred_gender',
        'preferred_age',
        'preferred_color',
        'living_environment',
        'activity_level',
        'pet_experience',
        'has_children',
        'has_other_pets',
        'hours_alone',
        'special_care_capacity',
        'desired_temperaments',
    ];

    protected $casts = [
        'has_children' => 'boolean',
        'has_other_pets' => 'boolean',
        'special_care_capacity' => 'boolean',
        'desired_temperaments' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
