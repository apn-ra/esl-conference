<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Observation;

use Apntalk\EslConference\Event\ConferenceMaintenanceEventFactory;
use Apntalk\EslConference\Observation\ConferenceObservationFactory;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;
use PHPUnit\Framework\TestCase;

final class PartialObservationBlockerTest extends TestCase
{
    public function testIncompleteEventObservationCarriesBlocker(): void
    {
        $parse = (new ConferenceMaintenanceEventFactory())->parse(FixtureLoader::eventHeaders('events/conference_missing_name.event'));
        self::assertNotNull($parse->event);

        $observation = (new ConferenceObservationFactory())->fromEvent($parse->event);

        self::assertContains(ConferenceBlocker::ConferenceObservationIncomplete, $observation->blockers);
    }
}
