<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\FixtureTest\Reply;

use Apntalk\EslConference\Parser\ConferenceListReplyParser;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;
use PHPUnit\Framework\TestCase;

final class ConferenceReplyLenientModeTest extends TestCase
{
    public function testLenientModeReturnsPartialSnapshotForBadMemberLine(): void
    {
        $reply = "Conference support-1001@default (1 members)\nmissing-fields";
        $result = (new ConferenceListReplyParser())->parse($reply);

        self::assertSame('partial', $result->status);
        self::assertContains(ConferenceBlocker::ConferenceReplyUnsupported, $result->blockers);
        self::assertNotNull($result->snapshot);
    }
}
