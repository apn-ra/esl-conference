<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Snapshot;

use Apntalk\EslConference\Model\ConferenceMemberIdentity;
use Apntalk\EslConference\Model\ConferenceMemberState;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;

final readonly class ConferenceMemberSnapshot
{
    /**
     * @param list<ConferenceBlocker> $blockers
     */
    public function __construct(
        public ConferenceMemberIdentity $identity,
        public ConferenceMemberState $state,
        public array $blockers = [],
    ) {
    }

    public function isComplete(): bool
    {
        return $this->blockers === [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'identity' => $this->identity->toArray(),
            'state' => $this->state->toArray(),
            'blockers' => array_map(static fn (ConferenceBlocker $blocker): string => $blocker->value, $this->blockers),
        ];
    }
}
