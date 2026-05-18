<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Model;

use Apntalk\EslConference\Exception\UnsafeConferenceCommandArgument;

final readonly class ConferenceCommandToken
{
    private const int MAX_LENGTH = 256;

    private function __construct(private string $raw)
    {
    }

    public static function fromString(string $raw): self
    {
        $value = trim($raw);

        if ($value === '' || strlen($value) > self::MAX_LENGTH) {
            throw new UnsafeConferenceCommandArgument('Conference command argument is empty or too long.');
        }

        if (preg_match('/[\r\n\0\s;&|`$<>]/', $value) === 1) {
            throw new UnsafeConferenceCommandArgument('Conference command argument contains unsafe command structure characters.');
        }

        return new self($value);
    }

    public function raw(): string
    {
        return $this->raw;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['raw' => $this->raw];
    }
}
