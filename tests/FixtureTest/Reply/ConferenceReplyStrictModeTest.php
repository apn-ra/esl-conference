<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\FixtureTest\Reply;

use Apntalk\EslConference\Parser\ConferenceListReplyParser;
use Apntalk\EslConference\Parser\ParseMode;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;
use PHPUnit\Framework\TestCase;

final class ConferenceReplyStrictModeTest extends TestCase
{
    public function testStrictModeFailsClosedOnUnsupportedReply(): void
    {
        $result = (new ConferenceListReplyParser())->parse(FixtureLoader::text('replies/conference_unparseable.txt'), ParseMode::Strict);

        self::assertSame('unknownFormat', $result->status);
        self::assertContains(ConferenceBlocker::ConferenceReplyUnsupported, $result->blockers);
    }

    public function testRejectedReplyFailsWithCommandBlocker(): void
    {
        $result = (new ConferenceListReplyParser())->parse(FixtureLoader::text('replies/conference_rejected.txt'), ParseMode::Strict);

        self::assertSame('failed', $result->status);
        self::assertContains(ConferenceBlocker::ConferenceCommandRejected, $result->blockers);
    }
}
