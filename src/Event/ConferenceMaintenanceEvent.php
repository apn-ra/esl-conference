<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Event;

use Apntalk\EslConference\Model\ConferenceAction;
use Apntalk\EslConference\Model\ConferenceCallerIdentity;
use Apntalk\EslConference\Model\ConferenceChannelIdentity;
use Apntalk\EslConference\Model\ConferenceChannelUuid;
use Apntalk\EslConference\Model\ConferenceMemberId;
use Apntalk\EslConference\Model\ConferenceMemberIdentity;
use Apntalk\EslConference\Model\ConferenceName;
use Apntalk\EslConference\Model\ConferenceProfile;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;

class ConferenceMaintenanceEvent
{
    /**
     * @param array<string, string> $rawSummary
     * @param list<ConferenceBlocker> $blockers
     */
    public function __construct(
        public readonly ConferenceAction $action,
        public readonly ?ConferenceName $conferenceName,
        public readonly ?ConferenceProfile $profile,
        public readonly ?int $conferenceSize,
        public readonly ConferenceMemberIdentity $member,
        public readonly array $rawSummary,
        public readonly array $blockers = [],
    ) {
    }

    /**
     * @param array<string, string> $headers
     * @param list<ConferenceBlocker> $blockers
     */
    public static function fromHeaders(ConferenceAction $action, array $headers, ConferenceEventFieldExtractor $extractor, array $blockers = []): self
    {
        $uuid = $extractor->get($headers, 'Unique-ID');

        return new self(
            $action,
            self::stringOrNull($extractor->get($headers, 'Conference-Name')) === null ? null : ConferenceName::fromObserved((string) $extractor->get($headers, 'Conference-Name')),
            self::stringOrNull($extractor->get($headers, 'Conference-Profile-Name')) === null ? null : new ConferenceProfile((string) $extractor->get($headers, 'Conference-Profile-Name')),
            self::integerOrNull($extractor->get($headers, 'Conference-Size')),
            new ConferenceMemberIdentity(
                ConferenceMemberId::fromString((string) ($extractor->get($headers, 'Member-ID') ?? '')),
                self::stringOrNull($extractor->get($headers, 'Member-Type')),
                new ConferenceChannelIdentity(
                    self::stringOrNull($extractor->get($headers, 'Channel-Name')),
                    $uuid === null ? null : ConferenceChannelUuid::fromString($uuid),
                ),
                new ConferenceCallerIdentity(
                    self::stringOrNull($extractor->get($headers, 'Caller-Caller-ID-Number')),
                    self::stringOrNull($extractor->get($headers, 'Caller-Caller-ID-Name')),
                    self::stringOrNull($extractor->get($headers, 'Caller-Destination-Number')),
                    self::stringOrNull($extractor->get($headers, 'Caller-Username')),
                    self::stringOrNull($extractor->get($headers, 'Caller-Context')),
                ),
            ),
            self::redactedSummary($headers),
            $blockers,
        );
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
            'action' => $this->action->raw(),
            'conference_name' => $this->conferenceName?->raw(),
            'profile' => $this->profile?->raw(),
            'conference_size' => $this->conferenceSize,
            'member' => $this->member->toArray(),
            'raw_summary' => $this->rawSummary,
            'blockers' => array_map(static fn (ConferenceBlocker $blocker): string => $blocker->value, $this->blockers),
        ];
    }

    private static function stringOrNull(?string $value): ?string
    {
        $trimmed = $value === null ? '' : trim($value);

        return $trimmed === '' ? null : $value;
    }

    private static function integerOrNull(?string $value): ?int
    {
        if ($value === null || preg_match('/^\d+$/', trim($value)) !== 1) {
            return null;
        }

        return (int) $value;
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    private static function redactedSummary(array $headers): array
    {
        $allowed = [
            'Event-Subclass',
            'Action',
            'Conference-Name',
            'Conference-Profile-Name',
            'Conference-Size',
            'Member-ID',
            'Member-Type',
            'Channel-Name',
            'Unique-ID',
            'Caller-Destination-Number',
            'Caller-Username',
            'Caller-Context',
        ];
        $summary = [];

        foreach ($allowed as $key) {
            foreach ($headers as $header => $value) {
                if (strcasecmp($header, $key) === 0) {
                    $summary[$key] = $value;
                }
            }
        }

        return $summary;
    }
}
