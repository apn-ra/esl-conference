<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Model;

final readonly class ConferenceMemberIdentity
{
    public function __construct(
        public ?ConferenceMemberId $memberId,
        public ?string $memberType,
        public ConferenceChannelIdentity $channel,
        public ConferenceCallerIdentity $caller,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'member_id' => $this->memberId?->raw(),
            'member_type' => $this->memberType,
            'channel' => $this->channel->toArray(),
            'caller' => $this->caller->toArray(),
        ];
    }
}
