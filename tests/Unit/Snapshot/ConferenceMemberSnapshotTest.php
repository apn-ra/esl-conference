<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Snapshot;

use Apntalk\EslConference\Parser\ConferenceMemberListParser;
use PHPUnit\Framework\TestCase;

final class ConferenceMemberSnapshotTest extends TestCase
{
    public function testMemberSnapshotReportsCompleteness(): void
    {
        $member = (new ConferenceMemberListParser())->parseLine('7;chan;uuid-7;Name;1001;hear');

        self::assertNotNull($member);
        self::assertTrue($member->isComplete());
        self::assertSame('uuid-7', $member->toArray()['identity']['channel']['channel_uuid']);
    }
}
