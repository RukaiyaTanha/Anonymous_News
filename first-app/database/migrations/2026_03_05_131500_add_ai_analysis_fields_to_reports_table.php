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
        Schema::table('reports', function (Blueprint $table) {
            if (! Schema::hasColumn('reports', 'duplicate_similarity_score')) {
                $table->decimal('duplicate_similarity_score', 5, 2)->nullable()->after('ai_confidence_score');
            }

            if (! Schema::hasColumn('reports', 'ai_realism_assessment')) {
                $table->string('ai_realism_assessment')->nullable()->after('duplicate_similarity_score');
            }

            if (! Schema::hasColumn('reports', 'ai_suspicious_indicators')) {
                $table->json('ai_suspicious_indicators')->nullable()->after('ai_realism_assessment');
            }

            if (! Schema::hasColumn('reports', 'ai_entities')) {
                $table->json('ai_entities')->nullable()->after('ai_suspicious_indicators');
            }

            if (! Schema::hasColumn('reports', 'ai_model')) {
                $table->string('ai_model')->nullable()->after('ai_entities');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $columns = [
                'duplicate_similarity_score',
                'ai_realism_assessment',
                'ai_suspicious_indicators',
                'ai_entities',
                'ai_model',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('reports', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
