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
        Schema::create('auto_backup_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id')->unique();
            $table->boolean('daily_enabled')->default(false);
            $table->boolean('weekly_enabled')->default(false);
            $table->boolean('monthly_enabled')->default(false);
            $table->time('backup_time')->default('03:00:00'); // Default backup time at 3 AM
            $table->unsignedTinyInteger('weekly_day')->default(0); // 0 = Sunday, 6 = Saturday
            $table->unsignedTinyInteger('monthly_day')->default(1); // Day of month (1-28)
            $table->timestamp('last_daily_backup')->nullable();
            $table->timestamp('last_weekly_backup')->nullable();
            $table->timestamp('last_monthly_backup')->nullable();
            $table->timestamps();

            $table->foreign('server_id')
                ->references('id')
                ->on('servers')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_backup_settings');
    }
};
