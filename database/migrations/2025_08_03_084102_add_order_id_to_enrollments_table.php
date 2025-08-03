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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('progress', 5, 2)->default(0.00);
            $table->timestamp('enrolled_at');
            $table->timestamp('completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign('order_id');
            $table->dropColumn('progress');
            $table->dropColumn('enrolled_at');
            $table->dropColumn('completed_at');
        });
    }
};
