<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Snapshot;

use Apntalk\EslConference\Model\ConferenceName;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;

final readonly class ConferenceRoomSnapshot
{
    /**
     * @param list<ConferenceMemberSnapshot> $members
     * @param list<ConferenceBlocker> $blockers
     */
    public function __construct(
        public ConferenceName $conferenceName,
        public ?int $reportedMemberCount,
        public array $members = [],
        public array $blockers = [],
    ) {
    }

    public function isComplete(): bool
    {
        return $this->blockers === [] && array_filter($this->members, static fn (ConferenceMemberSnapshot $member): bool => ! $member->isComplete()) === [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'conference_name' => $this->conferenceName->raw(),
            'reported_member_count' => $this->reportedMemberCount,
            'members' => array_map(static fn (ConferenceMemberSnapshot $member): array => $member->toArray(), $this->members),
            'blockers' => array_map(static fn (ConferenceBlocker $blocker): string => $blocker->value, $this->blockers),
        ];
    }
}
