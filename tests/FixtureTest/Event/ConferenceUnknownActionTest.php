<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\FixtureTest\Event;

use Apntalk\EslConference\Event\ConferenceMaintenanceEventFactory;
use Apntalk\EslConference\Event\ConferenceUnknownMaintenanceAction;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;
use PHPUnit\Framework\TestCase;

final class ConferenceUnknownActionTest extends TestCase
{
    public function testUnknownActionProducesBlockerBearingResult(): void
    {
        $result = (new ConferenceMaintenanceEventFactory())->parse(FixtureLoader::eventHeaders('events/conference_unknown_action.event'));

        self::assertSame('unknownAction', $result->status);
        self::assertInstanceOf(ConferenceUnknownMaintenanceAction::class, $result->event);
        self::assertContains(ConferenceBlocker::ConferenceActionUnknown, $result->blockers);
    }
}
