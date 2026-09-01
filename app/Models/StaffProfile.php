<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'staff_code',
        'full_name',
        'avatar',
        'position_title',
        'phone',
        'status',
        'specialization',
        'digital_signature_path',
    ];

    protected $appends = ['avatar_url', 'digital_signature_url'];

    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }

        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        $root = request() ? request()->getSchemeAndHttpHost() : config('app.url', 'http://localhost:8000');
        return $root . '/storage/' . ltrim($this->avatar, '/');
    }

    public function getDigitalSignatureUrlAttribute(): ?string
    {
        if (empty($this->digital_signature_path)) {
            return null;
        }

        if (str_starts_with($this->digital_signature_path, 'http')) {
            return $this->digital_signature_path;
        }

        $root = request() ? request()->getSchemeAndHttpHost() : config('app.url', 'http://localhost:8000');
        return $root . '/storage/' . ltrim($this->digital_signature_path, '/');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
