<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Reply;

use Apntalk\EslConference\Snapshot\ConferenceRoomSnapshot;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;

final readonly class ConferenceCommandParseResult
{
    /**
     * @param list<ConferenceBlocker> $blockers
     */
    private function __construct(
        public string $status,
        public ?ConferenceRoomSnapshot $snapshot,
        public array $blockers,
        public ?string $summary = null,
    ) {
    }

    public static function success(ConferenceRoomSnapshot $snapshot): self
    {
        return new self('ok', $snapshot, $snapshot->blockers);
    }

    /**
     * @param list<ConferenceBlocker> $blockers
     */
    public static function partial(ConferenceRoomSnapshot $snapshot, array $blockers): self
    {
        return new self('partial', $snapshot, $blockers);
    }

    /**
     * @param list<ConferenceBlocker> $blockers
     */
    public static function unknownFormat(array $blockers, string $summary): self
    {
        return new self('unknownFormat', null, $blockers, $summary);
    }

    /**
     * @param list<ConferenceBlocker> $blockers
     */
    public static function failed(array $blockers, string $summary): self
    {
        return new self('failed', null, $blockers, $summary);
    }

    public function ok(): bool
    {
        return $this->status === 'ok';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'snapshot' => $this->snapshot?->toArray(),
            'blockers' => array_map(static fn (ConferenceBlocker $blocker): string => $blocker->value, $this->blockers),
            'summary' => $this->summary,
        ];
    }
}
