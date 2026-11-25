<?php

namespace App\Notifications\Channels;

use App\Jobs\SendMessageToGoogleChatJob;
use App\Models\Team;
use App\Notifications\Dto\GoogleChatMessage;
use Illuminate\Notifications\Notification;

class GoogleChatChannel
{
    /**
     * Send the given notification.
     */
    public function send(Team $notifiable, Notification $notification): void
    {
        $googleChatSettings = $notifiable->googleChatNotificationSettings;

        if (! $googleChatSettings || ! $googleChatSettings->isEnabled() || ! $googleChatSettings->google_chat_webhook_url) {
            return;
        }

        $message = method_exists($notification, 'toGoogleChat')
            ? $notification->toGoogleChat()
            : GoogleChatMessage::fromSlack($notification->toSlack());

        SendMessageToGoogleChatJob::dispatch($message, $googleChatSettings->google_chat_webhook_url, data_get($notification, 'isTestNotification', false) ? null : $googleChatSettings);
    }
}
