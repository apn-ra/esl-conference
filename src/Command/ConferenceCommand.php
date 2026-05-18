<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Command;

interface ConferenceCommand
{
    public function toCommandText(): string;

    public function commandName(): string;
}
