<?php

use App\Models\InstanceSettings;
use App\Models\Team;
use App\Notifications\Channels\GoogleChatChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    InstanceSettings::unguarded(fn () => InstanceSettings::updateOrCreate(['id' => 0], ['id' => 0]));
});

it('includes GoogleChatChannel in enabled channels when google chat is enabled for the event', function () {
    $team = Team::factory()->create();
    $team->googleChatNotificationSettings->update([
        'google_chat_enabled' => true,
        'google_chat_webhook_url' => 'https://chat.googleapis.com/v1/spaces/AAA/messages?key=key&token=token',
        'deployment_success_google_chat_notifications' => true,
    ]);

    expect($team->getEnabledChannels('deployment_success'))->toContain(GoogleChatChannel::class);
});

it('excludes GoogleChatChannel when google chat is disabled', function () {
    $team = Team::factory()->create();
    $team->googleChatNotificationSettings->update([
        'google_chat_enabled' => false,
        'deployment_success_google_chat_notifications' => true,
    ]);

    expect($team->getEnabledChannels('deployment_success'))->not->toContain(GoogleChatChannel::class);
});
