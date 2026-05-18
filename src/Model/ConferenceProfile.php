<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Model;

final readonly class ConferenceProfile
{
    public function __construct(private string $raw)
    {
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
