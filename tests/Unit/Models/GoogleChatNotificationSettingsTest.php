<?php

use App\Livewire\Notifications\GoogleChat as GoogleChatNotification;
use App\Models\GoogleChatNotificationSettings;
use App\Models\InstanceSettings;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('auto-creates google chat notification settings when a team is created', function () {
    $team = Team::factory()->create();

    expect($team->googleChatNotificationSettings)->toBeInstanceOf(GoogleChatNotificationSettings::class)
        ->and($team->googleChatNotificationSettings->google_chat_enabled)->toBeFalse();
});

it('hides the google chat webhook url from serialization', function () {
    $settings = new GoogleChatNotificationSettings;

    expect($settings->getHidden())->toContain('google_chat_webhook_url');
});

it('encrypts google_chat_webhook_url at rest', function () {
    $team = Team::factory()->create();
    $team->googleChatNotificationSettings->update([
        'google_chat_webhook_url' => 'https://chat.googleapis.com/v1/spaces/AAA/messages?key=key&token=token',
    ]);

    $raw = DB::table('google_chat_notification_settings')
        ->where('team_id', $team->id)
        ->value('google_chat_webhook_url');

    expect($raw)->not->toBe('https://chat.googleapis.com/v1/spaces/AAA/messages?key=key&token=token');
});

it('backfills a missing settings row for a team that predates the table via the migration', function () {
    $team = Team::factory()->create();

    // Simulate a team whose settings row never got created (e.g. it existed
    // before this table/hook did) by deleting the row the `created` hook made.
    DB::table('google_chat_notification_settings')->where('team_id', $team->id)->delete();

    expect($team->fresh()->googleChatNotificationSettings)->toBeNull();

    // Re-run the actual migration. Without the fix this throws because
    // Schema::create() is called against a table that already exists;
    // with the fix it's guarded and falls through to the backfill loop.
    $migration = require database_path('migrations/2026_11_25_000001_create_google_chat_notification_settings_table.php');
    $migration->up();

    $settings = $team->fresh()->googleChatNotificationSettings;

    expect($settings)->toBeInstanceOf(GoogleChatNotificationSettings::class)
        ->and($settings->google_chat_enabled)->toBeFalse()
        ->and($settings->server_patch_google_chat_notifications)->toBeTrue();
});

it('mounts the GoogleChat Livewire component for a team backfilled after the fact', function () {
    InstanceSettings::unguarded(fn () => InstanceSettings::updateOrCreate(['id' => 0], ['id' => 0]));

    // Insert the team directly, bypassing Team::booted()'s `created` hook,
    // so no google_chat_notification_settings row gets auto-created —
    // mirroring a team that existed before this migration ran.
    $teamId = DB::table('teams')->insertGetId([
        'name' => 'Legacy Team',
        'personal_team' => false,
        'show_boarding' => false,
        'is_mcp_server_enabled' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $team = Team::findOrFail($teamId);

    expect($team->googleChatNotificationSettings)->toBeNull();

    // Manually insert the row the same way the migration's backfill does.
    DB::table('google_chat_notification_settings')->insert([
        'team_id' => $team->id,
        'google_chat_enabled' => false,
        'google_chat_webhook_url' => null,
        'deployment_success_google_chat_notifications' => false,
        'deployment_failure_google_chat_notifications' => true,
        'status_change_google_chat_notifications' => false,
        'restart_limit_reached_google_chat_notifications' => true,
        'backup_success_google_chat_notifications' => false,
        'backup_failure_google_chat_notifications' => true,
        'scheduled_task_success_google_chat_notifications' => false,
        'scheduled_task_failure_google_chat_notifications' => true,
        'docker_cleanup_success_google_chat_notifications' => false,
        'docker_cleanup_failure_google_chat_notifications' => true,
        'server_disk_usage_google_chat_notifications' => true,
        'server_reachable_google_chat_notifications' => false,
        'server_unreachable_google_chat_notifications' => true,
        'server_patch_google_chat_notifications' => true,
        'server_auto_update_google_chat_notifications' => true,
        'traefik_outdated_google_chat_notifications' => true,
        'image_vulnerabilities_google_chat_notifications' => false,
        'failure_count' => 0,
    ]);

    $admin = User::factory()->create();
    $admin->teams()->attach($team, ['role' => 'admin']);

    test()->actingAs($admin);
    session(['currentTeam' => $team]);

    Livewire::test(GoogleChatNotification::class)
        ->assertNotDispatched('error')
        ->assertSet('googleChatEnabled', false);
});
