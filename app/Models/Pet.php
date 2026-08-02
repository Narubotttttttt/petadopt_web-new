<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function adoptionApplications()
    {
        return $this->hasMany(AdoptionApplication::class);
    }

    public function temperamentTags()
    {
        return $this->belongsToMany(TemperamentTag::class, 'pet_temperament_tag');
    }
}