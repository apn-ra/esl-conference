<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\FixtureTest\Event;

use Apntalk\EslConference\Event\ConferenceMaintenanceEventFactory;
use Apntalk\EslConference\Event\ConferenceMemberJoinedEvent;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use PHPUnit\Framework\TestCase;

final class ConferenceMaintenanceEventFactoryTest extends TestCase
{
    public function testParsesJoinedFixture(): void
    {
        $result = (new ConferenceMaintenanceEventFactory())->parse(FixtureLoader::eventHeaders('events/conference_member_joined.event'));

        self::assertTrue($result->ok());
        self::assertInstanceOf(ConferenceMemberJoinedEvent::class, $result->event);
        self::assertSame('support-1001', $result->event->conferenceName?->raw());
        self::assertSame('1', $result->event->member->memberId?->raw());
    }
}
