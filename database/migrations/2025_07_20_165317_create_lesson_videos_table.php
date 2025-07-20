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
        Schema::create('lesson_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lesson_id');
            $table->string('video_url');
            $table->string('video_thumbnail')->nullable();
            $table->integer('video_duration')->nullable(); // in seconds
            $table->enum('video_quality', ['480p', '720p', '1080p', '4k'])->default('720p');
            $table->decimal('video_size', 10, 2)->nullable(); // MB, increased precision

            // Foreign keys
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_videos');
    }
};
