<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Observation;

use Apntalk\EslConference\Event\ConferenceMaintenanceEventFactory;
use Apntalk\EslConference\Model\ConferenceObservationTimestamp;
use Apntalk\EslConference\Observation\ConferenceObservationFactory;
use Apntalk\EslConference\Observation\RuntimeObservationContext;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class ObservationSerializationTest extends TestCase
{
    public function testSerializesWithGenericContext(): void
    {
        $parse = (new ConferenceMaintenanceEventFactory())->parse(FixtureLoader::eventHeaders('events/conference_member_left.event'));
        self::assertNotNull($parse->event);

        $observation = (new ConferenceObservationFactory())->fromEvent(
            $parse->event,
            ConferenceObservationTimestamp::fromDateTime(new DateTimeImmutable('2026-01-01T00:00:00+00:00')),
            new RuntimeObservationContext('conn-1', 'core-1', new DateTimeImmutable('2026-01-01T00:00:00+00:00'), 10, 'corr-1'),
        );

        self::assertSame('2026-01-01T00:00:00+00:00', $observation->toArray()['observed_at']);
        self::assertSame('conn-1', $observation->toArray()['context']['source_connection_id']);
    }

    public function testTimestampSerializesToAtomString(): void
    {
        $timestamp = ConferenceObservationTimestamp::fromDateTime(new DateTimeImmutable('2026-01-01T00:00:00+00:00'));
        $serialized = $timestamp->toArray();

        self::assertSame('string', gettype($serialized));
        self::assertSame('2026-01-01T00:00:00+00:00', $serialized);
        self::assertInstanceOf(DateTimeImmutable::class, DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $serialized));

        $parse = (new ConferenceMaintenanceEventFactory())->parse(FixtureLoader::eventHeaders('events/conference_member_left.event'));
        self::assertNotNull($parse->event);

        $observation = (new ConferenceObservationFactory())->fromEvent($parse->event, $timestamp);
        self::assertSame($serialized, $observation->toArray()['observed_at']);
    }
}
