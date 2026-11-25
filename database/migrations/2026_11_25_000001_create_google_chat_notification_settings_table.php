<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create table if it doesn't exist
        if (! Schema::hasTable('google_chat_notification_settings')) {
            Schema::create('google_chat_notification_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->cascadeOnDelete();

                $table->boolean('google_chat_enabled')->default(false);
                $table->text('google_chat_webhook_url')->nullable();

                $table->boolean('deployment_success_google_chat_notifications')->default(false);
                $table->boolean('deployment_failure_google_chat_notifications')->default(true);
                $table->boolean('status_change_google_chat_notifications')->default(false);
                $table->boolean('restart_limit_reached_google_chat_notifications')->default(true);
                $table->boolean('backup_success_google_chat_notifications')->default(false);
                $table->boolean('backup_failure_google_chat_notifications')->default(true);
                $table->boolean('scheduled_task_success_google_chat_notifications')->default(false);
                $table->boolean('scheduled_task_failure_google_chat_notifications')->default(true);
                $table->boolean('docker_cleanup_success_google_chat_notifications')->default(false);
                $table->boolean('docker_cleanup_failure_google_chat_notifications')->default(true);
                $table->boolean('server_disk_usage_google_chat_notifications')->default(true);
                $table->boolean('server_reachable_google_chat_notifications')->default(false);
                $table->boolean('server_unreachable_google_chat_notifications')->default(true);
                $table->boolean('server_patch_google_chat_notifications')->default(true);
                $table->boolean('server_auto_update_google_chat_notifications')->default(true);
                $table->boolean('traefik_outdated_google_chat_notifications')->default(true);
                $table->boolean('image_vulnerabilities_google_chat_notifications')->default(false);
                $table->integer('failure_count')->default(0);

                $table->unique(['team_id']);
            });
        }

        // Populate google chat notification settings for existing teams (only if they don't already have settings)
        DB::table('teams')->select('id')->chunkById(100, function ($teams) {
            DB::table('google_chat_notification_settings')->insertOrIgnore(
                $teams->map(fn ($team) => ['team_id' => $team->id])->all()
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_chat_notification_settings');
    }
};
