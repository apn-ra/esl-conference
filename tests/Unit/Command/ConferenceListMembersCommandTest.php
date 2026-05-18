<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Command;

use Apntalk\EslConference\Command\ConferenceListMembersCommand;
use Apntalk\EslConference\Model\ConferenceName;
use PHPUnit\Framework\TestCase;

final class ConferenceListMembersCommandTest extends TestCase
{
    public function testBuildsDeterministicCommandText(): void
    {
        $command = ConferenceListMembersCommand::forConference(ConferenceName::fromString('support-1001@default'));

        self::assertSame('conference support-1001@default list', $command->toCommandText());
        self::assertSame('conference list', $command->commandName());
    }
}
