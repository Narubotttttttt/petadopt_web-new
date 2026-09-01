<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'fcm_token'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = ['avatar_url', 'digital_signature_url'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getInitialsAttribute(): string
    {
        $name = trim($this->name ?? '');
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

    public function getAvatarAttribute(): ?string
    {
        if ($this->role === 'adopter') {
            return $this->adoptersProfile?->avatar;
        }
        return $this->staffProfile?->avatar;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->role === 'adopter') {
            return $this->adoptersProfile?->avatar_url;
        }
        return $this->staffProfile?->avatar_url;
    }

    public function getDigitalSignaturePathAttribute(): ?string
    {
        if ($this->role === 'adopter') {
            return $this->adoptersProfile?->digital_signature_path;
        }
        return $this->staffProfile?->digital_signature_path;
    }

    public function getDigitalSignatureUrlAttribute(): ?string
    {
        if ($this->role === 'adopter') {
            return $this->adoptersProfile?->digital_signature_url;
        }
        return $this->staffProfile?->digital_signature_url;
    }

    public function adopterPreference()
    {
        return $this->hasOne(AdopterPreference::class);
    }

    public function adoptionApplications()
    {
        return $this->hasMany(AdoptionApplication::class);
    }

    public function adoptersProfile()
    {
        return $this->hasOne(AdoptersProfile::class);
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function addedPets()
    {
        return $this->hasMany(Pet::class, 'added_by_user_id');
    }
}