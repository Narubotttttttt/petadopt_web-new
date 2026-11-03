<?php

namespace App\Models;

/**
 * Backward compatibility alias for AdopterCompatibilityProfile.
 * The underlying database table has been renamed to 'adopter_compatibility_profiles'.
 */
class AdopterPreference extends AdopterCompatibilityProfile
{
    protected $table = 'adopter_compatibility_profiles';
}
