<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Command;

use Apntalk\EslConference\Model\ConferenceCommandToken;
use Apntalk\EslConference\Model\ConferenceName;

final readonly class ConferenceListMembersCommand implements ConferenceCommand
{
    private function __construct(private ConferenceCommandToken $conference)
    {
    }

    public static function forConference(ConferenceName $conferenceName): self
    {
        return new self(ConferenceCommandToken::fromString($conferenceName->raw()));
    }

    public function toCommandText(): string
    {
        return sprintf('conference %s list', $this->conference->raw());
    }

    public function commandName(): string
    {
        return 'conference list';
    }
}
