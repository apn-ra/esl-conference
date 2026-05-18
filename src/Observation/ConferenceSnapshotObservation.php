<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Observation;

use Apntalk\EslConference\Model\ConferenceObservationTimestamp;
use Apntalk\EslConference\Snapshot\ConferenceRoomSnapshot;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;

final class ConferenceSnapshotObservation extends ConferenceObservation
{
    public function __construct(
        public readonly ConferenceRoomSnapshot $snapshot,
        ConferenceObservationTimestamp $observedAt,
        ObservationConfidence $confidence,
        ?RuntimeObservationContext $context = null,
    ) {
        parent::__construct(
            'snapshot',
            $snapshot->conferenceName,
            null,
            null,
            'snapshot',
            $observedAt,
            ObservationSource::CommandReply,
            $confidence,
            $snapshot->blockers,
            ['member_count' => (string) count($snapshot->members)],
            $context,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $base = parent::toArray();
        $base['snapshot'] = $this->snapshot->toArray();
        $base['blockers'] = array_map(static fn (ConferenceBlocker $blocker): string => $blocker->value, $this->snapshot->blockers);

        return $base;
    }
}
