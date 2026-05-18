<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\FixtureTest\Event;

use Apntalk\EslConference\Event\ConferenceMaintenanceEventFactory;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;
use PHPUnit\Framework\TestCase;

final class ConferenceNotMaintenanceEventTest extends TestCase
{
    public function testNonMaintenanceEventIsNotRecognized(): void
    {
        $result = (new ConferenceMaintenanceEventFactory())->parse(FixtureLoader::eventHeaders('events/non_conference_custom_event.event'));

        self::assertSame('notConferenceMaintenance', $result->status);
        self::assertContains(ConferenceBlocker::ConferenceEventSubclassMismatch, $result->blockers);
    }
}
