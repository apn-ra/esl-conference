<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\FixtureTest\Event;

use Apntalk\EslConference\Event\ConferenceMaintenanceEventFactory;
use Apntalk\EslConference\Event\ConferenceMemberJoinedEvent;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use PHPUnit\Framework\TestCase;

final class ConferenceMemberJoinedEventTest extends TestCase
{
    public function testJoinedWrapperSerializesStableFields(): void
    {
        $result = (new ConferenceMaintenanceEventFactory())->parse(FixtureLoader::eventHeaders('events/conference_member_joined.event'));

        self::assertInstanceOf(ConferenceMemberJoinedEvent::class, $result->event);
        self::assertSame('add-member', $result->event->toArray()['action']);
        self::assertSame([], $result->event->toArray()['blockers']);
    }
}
