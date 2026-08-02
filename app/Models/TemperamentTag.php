<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemperamentTag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function pets()
    {
        return $this->belongsToMany(Pet::class, 'pet_temperament_tag');
    }
}