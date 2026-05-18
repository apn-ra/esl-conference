<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Command;

use Apntalk\EslConference\Command\ConferenceListMembersCommand;
use Apntalk\EslConference\Exception\UnsafeConferenceCommandArgument;
use Apntalk\EslConference\Model\ConferenceName;
use PHPUnit\Framework\TestCase;

final class ConferenceCommandSafetyTest extends TestCase
{
    public function testCommandBuilderRevalidatesObservedNameBeforeOutboundUse(): void
    {
        $this->expectException(UnsafeConferenceCommandArgument::class);

        ConferenceListMembersCommand::forConference(ConferenceName::fromObserved('support-1001@default;status'));
    }
}
