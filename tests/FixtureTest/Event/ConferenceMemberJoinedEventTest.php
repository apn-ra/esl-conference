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

    public function testRawSummaryExcludesCallerIdentifyingHeaders(): void
    {
        $result = (new ConferenceMaintenanceEventFactory())->parse(FixtureLoader::eventHeaders('events/conference_member_joined.event'));

        self::assertInstanceOf(ConferenceMemberJoinedEvent::class, $result->event);

        $summary = $result->event->toArray()['raw_summary'];
        self::assertIsArray($summary);
        self::assertSame([
            'Event-Subclass',
            'Action',
            'Conference-Name',
            'Conference-Profile-Name',
            'Conference-Size',
            'Member-ID',
            'Member-Type',
            'Channel-Name',
            'Unique-ID',
        ], array_keys($summary));

        foreach ([
            'Caller-Destination-Number',
            'Caller-Username',
            'Caller-Context',
            'Caller-Caller-ID-Number',
            'Caller-Caller-ID-Name',
        ] as $removedKey) {
            self::assertArrayNotHasKey($removedKey, $summary);
        }
    }
}
