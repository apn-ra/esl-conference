<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\FixtureTest\Reply;

use Apntalk\EslConference\Model\ConferenceName;
use Apntalk\EslConference\Parser\ConferenceListReplyParser;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use PHPUnit\Framework\TestCase;

final class ConferenceListReplyParserTest extends TestCase
{
    public function testParsesMembersFixture(): void
    {
        $result = (new ConferenceListReplyParser())->parse(
            FixtureLoader::text('replies/conference_list_members.txt'),
            conferenceName: ConferenceName::fromObserved('support-1001'),
        );

        self::assertTrue($result->ok());
        self::assertSame('support-1001', $result->snapshot?->conferenceName->raw());
        self::assertNull($result->snapshot->reportedMemberCount);
        self::assertCount(1, $result->snapshot->members);
    }

    public function testParsesEmptyFixture(): void
    {
        $result = (new ConferenceListReplyParser())->parse(FixtureLoader::text('replies/conference_list_empty.txt'));

        self::assertTrue($result->ok());
        self::assertCount(0, $result->snapshot->members);
    }
}
