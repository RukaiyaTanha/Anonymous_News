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
        Schema::create('reports', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('category_id')->constrained()->onDelete('cascade');

        $table->string('title');
        $table->string('slug')->unique();
        $table->text('excerpt')->nullable();
        $table->longText('content');

        $table->enum('status', ['pending','under_review','verified','rejected'])
              ->default('pending');

        $table->decimal('ai_confidence_score', 5, 2)->nullable();
        $table->decimal('credibility_score', 5, 2)->nullable();
        $table->integer('view_count')->default(0);
        $table->boolean('is_featured')->default(false);
        $table->timestamp('published_at')->nullable();

        $table->text('moderator_note')->nullable();
        $table->foreignId('reviewed_by')->nullable()
              ->constrained('users')->nullOnDelete();
        $table->timestamp('reviewed_at')->nullable();

        $table->timestamps();
        $table->softDeletes();

        $table->index('status');
        $table->index('category_id');
        $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
