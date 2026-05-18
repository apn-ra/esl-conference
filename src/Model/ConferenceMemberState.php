<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Model;

final readonly class ConferenceMemberState
{
    /**
     * @param list<string> $flags
     */
    public function __construct(public array $flags = [])
    {
    }

    /**
     * @return array<string, list<string>>
     */
    public function toArray(): array
    {
        return ['flags' => $this->flags];
    }
}
