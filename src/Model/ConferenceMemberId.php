<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Model;

final readonly class ConferenceMemberId
{
    private function __construct(private string $raw)
    {
    }

    public static function fromString(string $raw): ?self
    {
        $value = trim($raw);

        if ($value === '' || preg_match('/^\d+$/', $value) !== 1) {
            return null;
        }

        return new self($value);
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public function toInt(): int
    {
        return (int) $this->raw;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['raw' => $this->raw];
    }
}
