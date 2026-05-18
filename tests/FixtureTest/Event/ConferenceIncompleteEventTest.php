<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\FixtureTest\Event;

use Apntalk\EslConference\Event\ConferenceMaintenanceEventFactory;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;
use PHPUnit\Framework\TestCase;

final class ConferenceIncompleteEventTest extends TestCase
{
    public function testMissingNameProducesIncompleteResult(): void
    {
        $result = (new ConferenceMaintenanceEventFactory())->parse(FixtureLoader::eventHeaders('events/conference_missing_name.event'));

        self::assertSame('incomplete', $result->status);
        self::assertContains(ConferenceBlocker::ConferenceNameMissing, $result->blockers);
        self::assertFalse($result->ok());
    }
}
