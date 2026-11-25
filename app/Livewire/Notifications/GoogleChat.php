<?php

namespace App\Livewire\Notifications;

use App\Livewire\Notifications\Concerns\TogglesNotificationEvents;
use App\Models\GoogleChatNotificationSettings;
use App\Models\Team;
use App\Notifications\Test;
use App\Rules\SafeWebhookUrl;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class GoogleChat extends Component
{
    use AuthorizesRequests, TogglesNotificationEvents;

    protected $listeners = ['refresh' => '$refresh'];

    #[Locked]
    public Team $team;

    #[Locked]
    public GoogleChatNotificationSettings $settings;

    #[Validate(['boolean'])]
    public bool $googleChatEnabled = false;

    #[Validate(['required_if:googleChatEnabled,true', 'nullable', new SafeWebhookUrl])]
    public ?string $googleChatWebhookUrl = null;

    #[Validate(['boolean'])]
    public bool $deploymentSuccessGoogleChatNotifications = false;

    #[Validate(['boolean'])]
    public bool $deploymentFailureGoogleChatNotifications = true;

    #[Validate(['boolean'])]
    public bool $statusChangeGoogleChatNotifications = false;

    #[Validate(['boolean'])]
    public bool $restartLimitReachedGoogleChatNotifications = true;

    #[Validate(['boolean'])]
    public bool $backupSuccessGoogleChatNotifications = false;

    #[Validate(['boolean'])]
    public bool $backupFailureGoogleChatNotifications = true;

    #[Validate(['boolean'])]
    public bool $scheduledTaskSuccessGoogleChatNotifications = false;

    #[Validate(['boolean'])]
    public bool $scheduledTaskFailureGoogleChatNotifications = true;

    #[Validate(['boolean'])]
    public bool $dockerCleanupSuccessGoogleChatNotifications = false;

    #[Validate(['boolean'])]
    public bool $dockerCleanupFailureGoogleChatNotifications = true;

    #[Validate(['boolean'])]
    public bool $serverDiskUsageGoogleChatNotifications = true;

    #[Validate(['boolean'])]
    public bool $serverReachableGoogleChatNotifications = false;

    #[Validate(['boolean'])]
    public bool $serverUnreachableGoogleChatNotifications = true;

    #[Validate(['boolean'])]
    public bool $serverPatchGoogleChatNotifications = false;

    #[Validate(['boolean'])]
    public bool $serverAutoUpdateGoogleChatNotifications = true;

    #[Validate(['boolean'])]
    public bool $traefikOutdatedGoogleChatNotifications = true;

    #[Validate(['boolean'])]
    public bool $imageVulnerabilitiesGoogleChatNotifications = false;

    public function mount()
    {
        try {
            $this->team = auth()->user()->currentTeam();
            $this->settings = $this->team->googleChatNotificationSettings;
            $this->authorize('view', $this->settings);
            $this->syncData();
        } catch (\Throwable $e) {
            return handleError($e, $this);
        }
    }

    private function syncData(bool $toModel = false): void
    {
        if ($toModel) {
            $this->validate();
            $this->settings->google_chat_enabled = $this->googleChatEnabled;
            $this->settings->google_chat_webhook_url = $this->googleChatWebhookUrl;

            $this->settings->deployment_success_google_chat_notifications = $this->deploymentSuccessGoogleChatNotifications;
            $this->settings->deployment_failure_google_chat_notifications = $this->deploymentFailureGoogleChatNotifications;
            $this->settings->status_change_google_chat_notifications = $this->statusChangeGoogleChatNotifications;
            $this->settings->restart_limit_reached_google_chat_notifications = $this->restartLimitReachedGoogleChatNotifications;
            $this->settings->backup_success_google_chat_notifications = $this->backupSuccessGoogleChatNotifications;
            $this->settings->backup_failure_google_chat_notifications = $this->backupFailureGoogleChatNotifications;
            $this->settings->scheduled_task_success_google_chat_notifications = $this->scheduledTaskSuccessGoogleChatNotifications;
            $this->settings->scheduled_task_failure_google_chat_notifications = $this->scheduledTaskFailureGoogleChatNotifications;
            $this->settings->docker_cleanup_success_google_chat_notifications = $this->dockerCleanupSuccessGoogleChatNotifications;
            $this->settings->docker_cleanup_failure_google_chat_notifications = $this->dockerCleanupFailureGoogleChatNotifications;
            $this->settings->server_disk_usage_google_chat_notifications = $this->serverDiskUsageGoogleChatNotifications;
            $this->settings->server_reachable_google_chat_notifications = $this->serverReachableGoogleChatNotifications;
            $this->settings->server_unreachable_google_chat_notifications = $this->serverUnreachableGoogleChatNotifications;
            $this->settings->server_patch_google_chat_notifications = $this->serverPatchGoogleChatNotifications;
            $this->settings->server_auto_update_google_chat_notifications = $this->serverAutoUpdateGoogleChatNotifications;
            $this->settings->traefik_outdated_google_chat_notifications = $this->traefikOutdatedGoogleChatNotifications;
            $this->settings->image_vulnerabilities_google_chat_notifications = $this->imageVulnerabilitiesGoogleChatNotifications;

            $changedFields = array_keys($this->settings->getDirty());
            $this->settings->save();
            $this->auditNotificationSettings($changedFields);
            refreshSession();
        } else {
            $this->googleChatEnabled = $this->settings->google_chat_enabled;
            $this->googleChatWebhookUrl = auth()->user()->can('update', $this->settings)
                ? $this->settings->google_chat_webhook_url
                : null;

            $this->deploymentSuccessGoogleChatNotifications = $this->settings->deployment_success_google_chat_notifications;
            $this->deploymentFailureGoogleChatNotifications = $this->settings->deployment_failure_google_chat_notifications;
            $this->statusChangeGoogleChatNotifications = $this->settings->status_change_google_chat_notifications;
            $this->restartLimitReachedGoogleChatNotifications = $this->settings->restart_limit_reached_google_chat_notifications;
            $this->backupSuccessGoogleChatNotifications = $this->settings->backup_success_google_chat_notifications;
            $this->backupFailureGoogleChatNotifications = $this->settings->backup_failure_google_chat_notifications;
            $this->scheduledTaskSuccessGoogleChatNotifications = $this->settings->scheduled_task_success_google_chat_notifications;
            $this->scheduledTaskFailureGoogleChatNotifications = $this->settings->scheduled_task_failure_google_chat_notifications;
            $this->dockerCleanupSuccessGoogleChatNotifications = $this->settings->docker_cleanup_success_google_chat_notifications;
            $this->dockerCleanupFailureGoogleChatNotifications = $this->settings->docker_cleanup_failure_google_chat_notifications;
            $this->serverDiskUsageGoogleChatNotifications = $this->settings->server_disk_usage_google_chat_notifications;
            $this->serverReachableGoogleChatNotifications = $this->settings->server_reachable_google_chat_notifications;
            $this->serverUnreachableGoogleChatNotifications = $this->settings->server_unreachable_google_chat_notifications;
            $this->serverPatchGoogleChatNotifications = $this->settings->server_patch_google_chat_notifications;
            $this->serverAutoUpdateGoogleChatNotifications = $this->settings->server_auto_update_google_chat_notifications;
            $this->traefikOutdatedGoogleChatNotifications = $this->settings->traefik_outdated_google_chat_notifications;
            $this->imageVulnerabilitiesGoogleChatNotifications = $this->settings->image_vulnerabilities_google_chat_notifications;
        }
    }

    public function instantSaveGoogleChatEnabled()
    {
        try {
            $this->validate([
                'googleChatWebhookUrl' => 'required',
            ], [
                'googleChatWebhookUrl.required' => 'Google Chat Webhook URL is required.',
            ]);
            $this->saveModel();
        } catch (\Throwable $e) {
            $this->googleChatEnabled = (bool) $this->settings->refresh()->google_chat_enabled;

            return handleError($e, $this);
        } finally {
            $this->dispatch('refresh');
        }
    }

    public function toggleGoogleChatEnabled()
    {
        try {
            $this->resetErrorBag();

            if ($this->googleChatEnabled) {
                $this->googleChatEnabled = false;
            } else {
                $this->validate([
                    'googleChatWebhookUrl' => 'required',
                ], [
                    'googleChatWebhookUrl.required' => 'Google Chat Webhook URL is required.',
                ]);
                $this->googleChatEnabled = true;
            }

            $this->saveModel();
        } catch (\Throwable $e) {
            $this->syncData();

            return handleError($e, $this);
        } finally {
            $this->dispatch('refresh');
        }
    }

    public function submit()
    {
        try {
            $this->resetErrorBag();
            $this->saveModel();
        } catch (\Throwable $e) {
            return handleError($e, $this);
        }
    }

    public function saveModel()
    {
        $this->authorize('update', $this->settings);

        $this->syncData(true);
        refreshSession();
        $this->dispatch('success', 'Settings saved.');
    }

    public function sendTestNotification()
    {
        try {
            $this->authorize('sendTest', $this->settings);
            $this->team->notify(new Test(channel: 'google_chat'));
            $this->dispatch('success', 'Test notification sent.');
        } catch (\Throwable $e) {
            return handleError($e, $this);
        }
    }

    public function render()
    {
        return view('livewire.notifications.googlechat');
    }

    private function auditNotificationSettings(array $changedFields): void
    {
        if ($changedFields !== []) {
            auditLog('ui.notifications.google_chat.updated', ['team_id' => $this->team->id, 'changed_fields' => $changedFields]);
        }
    }
}
