<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\FixtureTest\Reply;

use Apntalk\EslConference\Parser\ConferenceMemberListParser;
use PHPUnit\Framework\TestCase;

final class ConferenceMemberListParserTest extends TestCase
{
    public function testParsesMemberLine(): void
    {
        $member = (new ConferenceMemberListParser())->parseLine('7;sofia/internal/1001@example.test;uuid-7;Name;1001;hear|speak');

        self::assertNotNull($member);
        self::assertSame('7', $member->identity->memberId?->raw());
        self::assertSame(['hear', 'speak'], $member->state->flags);
    }
}
