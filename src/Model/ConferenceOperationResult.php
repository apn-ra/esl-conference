<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Model;

use Apntalk\EslConference\Vocabulary\ConferenceBlocker;

final readonly class ConferenceOperationResult
{
    /**
     * @param list<ConferenceBlocker> $blockers
     */
    public function __construct(
        public bool $ok,
        public array $blockers = [],
        public ?string $summary = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'blockers' => array_map(static fn (ConferenceBlocker $blocker): string => $blocker->value, $this->blockers),
            'summary' => $this->summary,
        ];
    }
}
