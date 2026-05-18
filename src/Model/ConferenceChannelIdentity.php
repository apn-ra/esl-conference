<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Model;

final readonly class ConferenceChannelIdentity
{
    public function __construct(
        public ?string $channelName,
        public ?ConferenceChannelUuid $channelUuid,
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'channel_name' => $this->channelName,
            'channel_uuid' => $this->channelUuid?->raw(),
        ];
    }
}
