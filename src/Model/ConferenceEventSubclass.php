<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Model;

final readonly class ConferenceEventSubclass
{
    public const string CONFERENCE_MAINTENANCE = 'conference::maintenance';

    private function __construct(private string $raw)
    {
    }

    public static function fromString(string $raw): ?self
    {
        $value = trim($raw);

        if ($value === '') {
            return null;
        }

        return new self($value);
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public function isConferenceMaintenance(): bool
    {
        return $this->raw === self::CONFERENCE_MAINTENANCE;
    }
}
