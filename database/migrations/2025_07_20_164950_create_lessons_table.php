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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('lesson_type', ['video', 'article', 'quiz', 'assignment']);
            $table->integer('order_index');
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_free_preview')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');

            // Index for course_id and order_index
            $table->index(['section_id', 'order_index'], 'idx_section_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
