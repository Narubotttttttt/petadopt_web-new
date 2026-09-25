<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('adoption_applications', 'documents_verified_at')) {
                $table->timestamp('documents_verified_at')->nullable()->after('staff_signed_at');
            }
            if (!Schema::hasColumn('adoption_applications', 'documents_verified_by')) {
                $table->foreignId('documents_verified_by')->nullable()->after('documents_verified_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('adoption_applications', 'id_document_verified')) {
                $table->boolean('id_document_verified')->default(false)->after('documents_verified_by');
            }
            if (!Schema::hasColumn('adoption_applications', 'barangay_cert_verified')) {
                $table->boolean('barangay_cert_verified')->default(false)->after('id_document_verified');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->dropForeign(['documents_verified_by']);
            $table->dropColumn([
                'documents_verified_at',
                'documents_verified_by',
                'id_document_verified',
                'barangay_cert_verified',
            ]);
        });
    }
};
