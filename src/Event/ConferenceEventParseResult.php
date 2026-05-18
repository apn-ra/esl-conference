<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Event;

use Apntalk\EslConference\Vocabulary\ConferenceBlocker;

final readonly class ConferenceEventParseResult
{
    /**
     * @param array<string, string> $rawSummary
     * @param list<ConferenceBlocker> $blockers
     */
    private function __construct(
        public string $status,
        public ?ConferenceMaintenanceEvent $event,
        public array $blockers,
        public array $rawSummary = [],
    ) {
    }

    public static function recognized(ConferenceMaintenanceEvent $event): self
    {
        return new self('recognized', $event, $event->blockers);
    }

    /**
     * @param list<ConferenceBlocker> $blockers
     */
    public static function unknownAction(ConferenceUnknownMaintenanceAction $event, array $blockers): self
    {
        return new self('unknownAction', $event, $blockers);
    }

    /**
     * @param array<string, string> $rawSummary
     * @param list<ConferenceBlocker> $blockers
     */
    public static function notConferenceMaintenance(array $rawSummary, array $blockers): self
    {
        return new self('notConferenceMaintenance', null, $blockers, $rawSummary);
    }

    /**
     * @param array<string, string> $rawSummary
     * @param list<ConferenceBlocker> $blockers
     */
    public static function incomplete(array $rawSummary, array $blockers, ?ConferenceMaintenanceEvent $event = null): self
    {
        return new self('incomplete', $event, $blockers, $rawSummary);
    }

    public function ok(): bool
    {
        return $this->status === 'recognized' && $this->blockers === [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'event' => $this->event?->toArray(),
            'blockers' => array_map(static fn (ConferenceBlocker $blocker): string => $blocker->value, $this->blockers),
            'raw_summary' => $this->rawSummary,
        ];
    }
}
