<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Observation;

use Apntalk\EslConference\Event\ConferenceMaintenanceEventFactory;
use Apntalk\EslConference\Observation\ConferenceObservationFactory;
use Apntalk\EslConference\Observation\ObservationConfidence;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use PHPUnit\Framework\TestCase;

final class MemberPresenceObservationTest extends TestCase
{
    public function testUnknownActionCreatesUnknownPresenceObservation(): void
    {
        $parse = (new ConferenceMaintenanceEventFactory())->parse(FixtureLoader::eventHeaders('events/conference_unknown_action.event'));
        self::assertNotNull($parse->event);

        $observation = (new ConferenceObservationFactory())->fromEvent($parse->event);

        self::assertSame('member_presence', $observation->type);
        self::assertSame(ObservationConfidence::Unknown, $observation->confidence);
    }
}
