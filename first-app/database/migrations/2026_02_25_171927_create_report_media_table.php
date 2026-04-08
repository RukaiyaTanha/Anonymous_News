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
        Schema::create('report_media', function (Blueprint $table) {
        $table->id();
        $table->foreignId('report_id')->constrained()->onDelete('cascade');
        $table->string('file_path');
        $table->enum('media_type', ['image','video','document']);
        $table->timestamp('created_at')->nullable();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_media');
    }
};
