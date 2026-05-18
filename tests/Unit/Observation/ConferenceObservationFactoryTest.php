<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Observation;

use Apntalk\EslConference\Event\ConferenceMaintenanceEventFactory;
use Apntalk\EslConference\Observation\ConferenceObservationFactory;
use Apntalk\EslConference\Observation\MemberJoinedObservation;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use PHPUnit\Framework\TestCase;

final class ConferenceObservationFactoryTest extends TestCase
{
    public function testCreatesJoinedObservationFromEvent(): void
    {
        $parse = (new ConferenceMaintenanceEventFactory())->parse(FixtureLoader::eventHeaders('events/conference_member_joined.event'));
        self::assertNotNull($parse->event);

        $observation = (new ConferenceObservationFactory())->fromEvent($parse->event);

        self::assertInstanceOf(MemberJoinedObservation::class, $observation);
        self::assertSame('member_joined', $observation->toArray()['type']);
    }
}
