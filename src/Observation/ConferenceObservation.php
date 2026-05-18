<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Observation;

use Apntalk\EslConference\Model\ConferenceChannelUuid;
use Apntalk\EslConference\Model\ConferenceMemberId;
use Apntalk\EslConference\Model\ConferenceName;
use Apntalk\EslConference\Model\ConferenceObservationTimestamp;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;

class ConferenceObservation
{
    /**
     * @param list<ConferenceBlocker> $blockers
     * @param array<string, string> $rawSummary
     */
    public function __construct(
        public readonly string $type,
        public readonly ?ConferenceName $conferenceName,
        public readonly ?ConferenceMemberId $memberId,
        public readonly ?ConferenceChannelUuid $channelUuid,
        public readonly ?string $action,
        public readonly ConferenceObservationTimestamp $observedAt,
        public readonly ObservationSource $source,
        public readonly ObservationConfidence $confidence,
        public readonly array $blockers,
        public readonly array $rawSummary = [],
        public readonly ?RuntimeObservationContext $context = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'conference_name' => $this->conferenceName?->raw(),
            'member_id' => $this->memberId?->raw(),
            'channel_uuid' => $this->channelUuid?->raw(),
            'action' => $this->action,
            'observed_at' => $this->observedAt->toArray(),
            'source' => $this->source->name,
            'confidence' => $this->confidence->name,
            'blockers' => array_map(static fn (ConferenceBlocker $blocker): string => $blocker->value, $this->blockers),
            'raw_summary' => $this->rawSummary,
            'context' => $this->context?->toArray(),
        ];
    }
}
