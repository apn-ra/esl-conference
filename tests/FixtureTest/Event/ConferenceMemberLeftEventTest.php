<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\FixtureTest\Event;

use Apntalk\EslConference\Event\ConferenceMaintenanceEventFactory;
use Apntalk\EslConference\Event\ConferenceMemberLeftEvent;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use PHPUnit\Framework\TestCase;

final class ConferenceMemberLeftEventTest extends TestCase
{
    public function testParsesLeftFixture(): void
    {
        $result = (new ConferenceMaintenanceEventFactory())->parse(FixtureLoader::eventHeaders('events/conference_member_left.event'));

        self::assertTrue($result->ok());
        self::assertInstanceOf(ConferenceMemberLeftEvent::class, $result->event);
        self::assertSame('del-member', $result->event->action->raw());
    }
}
