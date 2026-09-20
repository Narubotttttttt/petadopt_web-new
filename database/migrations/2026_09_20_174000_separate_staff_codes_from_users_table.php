<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\StaffProfile;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Re-sequence staff codes independently from users.id
        // 1. Admins
        $admins = StaffProfile::whereHas('user', function ($q) {
            $q->where('role', 'admin');
        })->orWhere('staff_code', 'like', 'ADM-%')
          ->orderBy('id')
          ->get();

        $adminSeq = 1;
        foreach ($admins as $prof) {
            $prof->staff_code = sprintf('ADM-%04d', $adminSeq++);
            $prof->save();
        }

        // 2. Staff
        $staff = StaffProfile::whereHas('user', function ($q) {
            $q->where('role', 'staff');
        })->orWhere('staff_code', 'like', 'STF-%')
          ->orderBy('id')
          ->get();

        $staffSeq = 1;
        foreach ($staff as $prof) {
            // Avoid collision if already updated
            $prof->staff_code = sprintf('STF-%04d', $staffSeq++);
            $prof->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
