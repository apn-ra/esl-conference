<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Integration;

use Apntalk\EslConference\Command\ConferenceListMembersCommand;
use Apntalk\EslConference\Event\ConferenceMaintenanceEventFactory;
use Apntalk\EslConference\Model\ConferenceName;
use Apntalk\EslConference\Observation\ConferenceObservationFactory;
use Apntalk\EslConference\Parser\ConferenceListReplyParser;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use PHPUnit\Framework\TestCase;

final class EventReplyObservationIntegrationTest extends TestCase
{
    public function testStableSliceWorksTogetherWithoutTransport(): void
    {
        $command = ConferenceListMembersCommand::forConference(ConferenceName::fromString('support-1001@default'));
        self::assertSame('conference support-1001@default list', $command->toCommandText());

        $eventResult = (new ConferenceMaintenanceEventFactory())->parse(FixtureLoader::eventHeaders('events/conference_member_joined.event'));
        self::assertNotNull($eventResult->event);
        $eventObservation = (new ConferenceObservationFactory())->fromEvent($eventResult->event);
        self::assertSame('member_joined', $eventObservation->type);

        $replyResult = (new ConferenceListReplyParser())->parse(
            FixtureLoader::text('replies/conference_list_members.txt'),
            conferenceName: ConferenceName::fromObserved('support-1001'),
        );
        self::assertNotNull($replyResult->snapshot);
        $snapshotObservation = (new ConferenceObservationFactory())->fromSnapshot($replyResult->snapshot);
        self::assertSame('snapshot', $snapshotObservation->type);
    }
}
