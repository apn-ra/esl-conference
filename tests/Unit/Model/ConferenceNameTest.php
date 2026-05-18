<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Model;

use Apntalk\EslConference\Exception\UnsafeConferenceCommandArgument;
use Apntalk\EslConference\Model\ConferenceName;
use PHPUnit\Framework\TestCase;

final class ConferenceNameTest extends TestCase
{
    public function testParsesRoomAndProfileCandidates(): void
    {
        $name = ConferenceName::fromString('support-1001@default');

        self::assertSame('support-1001', $name->roomNameCandidate());
        self::assertSame('default', $name->profileNameCandidate());
        self::assertTrue($name->hasProfileCandidate());
    }

    public function testRejectsUnsafeOutboundName(): void
    {
        $this->expectException(UnsafeConferenceCommandArgument::class);

        ConferenceName::fromString("room\nlist");
    }
}
