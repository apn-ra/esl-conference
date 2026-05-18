<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Model;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

final readonly class ConferenceObservationTimestamp
{
    private function __construct(private DateTimeImmutable $value)
    {
    }

    public static function now(): self
    {
        return new self(new DateTimeImmutable('now', new DateTimeZone('UTC')));
    }

    public static function fromDateTime(DateTimeImmutable $value): self
    {
        return new self($value);
    }

    public function value(): DateTimeImmutable
    {
        return $this->value;
    }

    public function toArray(): string
    {
        return $this->value->format(DateTimeInterface::ATOM);
    }
}
