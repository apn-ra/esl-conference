<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Observation;

use DateTimeImmutable;
use DateTimeInterface;

final readonly class RuntimeObservationContext
{
    public function __construct(
        public ?string $sourceConnectionId = null,
        public ?string $sourceCoreUuid = null,
        public ?DateTimeImmutable $receivedAt = null,
        public ?int $eventSequence = null,
        public ?string $correlationId = null,
    ) {
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'source_connection_id' => $this->sourceConnectionId,
            'source_core_uuid' => $this->sourceCoreUuid,
            'received_at' => $this->receivedAt?->format(DateTimeInterface::ATOM),
            'event_sequence' => $this->eventSequence,
            'correlation_id' => $this->correlationId,
        ];
    }
}
