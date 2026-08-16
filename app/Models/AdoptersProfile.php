<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdoptersProfile extends Model
{
    use HasFactory;

    protected $table = 'adopters_profile';

    protected $fillable = [
        'user_id',
        'adopter_code',
        'full_name',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'status',
        'admin_notes',
        'last_check_in_date',
    ];

    protected $casts = [
        'last_check_in_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adoptionApplications(): HasMany
    {
        return $this->hasMany(AdoptionApplication::class, 'applicant_email', 'email');
    }

    public function getInitialsAttribute(): string
    {
        $name = trim($this->full_name ?? '');
        if (empty($name)) {
            return 'U';
        }

        $parts = preg_split('/\s+/', $name);
        if (count($parts) >= 2) {
            $first = mb_substr($parts[0], 0, 1);
            $last = mb_substr(end($parts), 0, 1);
            return strtoupper($first . $last);
        }

        return strtoupper(mb_substr($name, 0, 1));
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->user && $this->user->avatar_url) {
            return $this->user->avatar_url;
        }
        return null;
    }
}