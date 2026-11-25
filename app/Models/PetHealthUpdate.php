<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'review_decision',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'weight' => 'float',
        'reviewed_at' => 'datetime',
    ];

    protected $appends = [
        'photo_url',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        if (empty($this->photo_path)) {
            return null;
        }

        $path = $this->photo_path;

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            if (preg_match('#^https?://(?:localhost|127\.0\.0\.1|[^/]+\.trycloudflare\.com)(?::\d+)?/storage/(.+)$#i', $path, $matches)) {
                $path = $matches[1];
            } else {
                return $path;
            }
        }

        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, strlen('storage/'));
        } elseif (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, strlen('public/'));
        }

        return asset('storage/' . $cleanPath);
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

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}