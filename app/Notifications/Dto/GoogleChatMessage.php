<?php

namespace App\Notifications\Dto;

class GoogleChatMessage
{
    public function __construct(
        public string $title,
        public string $description,
    ) {}

    public static function fromSlack(SlackMessage $slack): self
    {
        return new self(
            title: $slack->title,
            description: str_replace([':warning:', ':link:', ':book:'], ['⚠️', '🔗', '📖'], $slack->description),
        );
    }

    public function toPayload(): array
    {
        $footerText = isCloud() ? 'Coolify Cloud' : 'Coolify v'.config('constants.coolify.version');

        $text = "*{$this->title}*";

        if ($this->description !== '') {
            $text .= "\n\n{$this->description}";
        }

        $text .= "\n\n_{$footerText}_";

        return ['text' => $text];
    }
}
