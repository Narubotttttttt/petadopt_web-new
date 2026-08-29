<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->foreignId('evaluator_id')->nullable()->after('event_notes')->constrained('users')->nullOnDelete();
            $table->string('evaluator_name')->nullable()->after('evaluator_id');
            $table->string('evaluation_recommendation')->nullable()->after('evaluator_name');
            $table->text('evaluation_notes')->nullable()->after('evaluation_recommendation');
            $table->timestamp('evaluated_at')->nullable()->after('evaluation_notes');
        });
    }

    public function down(): void
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->dropForeign(['evaluator_id']);
            $table->dropColumn([
                'evaluator_id',
                'evaluator_name',
                'evaluation_recommendation',
                'evaluation_notes',
                'evaluated_at',
            ]);
        });
    }
};
