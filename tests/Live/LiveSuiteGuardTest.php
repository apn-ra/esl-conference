<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Live;

use PHPUnit\Framework\TestCase;

final class LiveSuiteGuardTest extends TestCase
{
    public function testLiveSuiteIsOperatorRunOnly(): void
    {
        if (getenv('ESL_CONFERENCE_LIVE_TEST') !== '1') {
            self::markTestSkipped('Live FreeSWITCH validation is operator-run only.');
        }

        self::assertSame('1', getenv('ESL_CONFERENCE_LIVE_TEST'));
    }
}
