<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Model;

final readonly class ConferenceMemberSelector
{
    private function __construct(private ConferenceCommandToken $token)
    {
    }

    public static function fromMemberId(ConferenceMemberId $memberId): self
    {
        return new self(ConferenceCommandToken::fromString($memberId->raw()));
    }

    public function raw(): string
    {
        return $this->token->raw();
    }
}
