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
        'administered_by',
        'next_due_date',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'next_due_date' => 'date',
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}