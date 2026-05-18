<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Observation;

use Apntalk\EslConference\Event\ConferenceMaintenanceEvent;
use Apntalk\EslConference\Event\ConferenceMemberJoinedEvent;
use Apntalk\EslConference\Event\ConferenceMemberLeftEvent;
use Apntalk\EslConference\Model\ConferenceObservationTimestamp;
use Apntalk\EslConference\Snapshot\ConferenceRoomSnapshot;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;

final readonly class ConferenceObservationFactory
{
    public function fromEvent(
        ConferenceMaintenanceEvent $event,
        ?ConferenceObservationTimestamp $observedAt = null,
        ?RuntimeObservationContext $context = null,
    ): ConferenceObservation {
        $timestamp = $observedAt ?? ConferenceObservationTimestamp::now();
        $confidence = $event->blockers === [] ? ObservationConfidence::Observed : ObservationConfidence::Partial;
        $blockers = $event->blockers;

        if ($event->conferenceName === null || $event->member->memberId === null || $event->member->channel->channelUuid === null) {
            $blockers[] = ConferenceBlocker::ConferenceObservationIncomplete;
            $confidence = ObservationConfidence::Partial;
        }

        if ($event instanceof ConferenceMemberJoinedEvent) {
            return new MemberJoinedObservation(
                'member_joined',
                $event->conferenceName,
                $event->member->memberId,
                $event->member->channel->channelUuid,
                $event->action->raw(),
                $timestamp,
                ObservationSource::Event,
                $confidence,
                array_values(array_unique($blockers, SORT_REGULAR)),
                $event->rawSummary,
                $context,
            );
        }

        if ($event instanceof ConferenceMemberLeftEvent) {
            return new MemberLeftObservation(
                'member_left',
                $event->conferenceName,
                $event->member->memberId,
                $event->member->channel->channelUuid,
                $event->action->raw(),
                $timestamp,
                ObservationSource::Event,
                $confidence,
                array_values(array_unique($blockers, SORT_REGULAR)),
                $event->rawSummary,
                $context,
            );
        }

        return new MemberPresenceObservation(
            'member_presence',
            $event->conferenceName,
            $event->member->memberId,
            $event->member->channel->channelUuid,
            $event->action->raw(),
            $timestamp,
            ObservationSource::Event,
            ObservationConfidence::Unknown,
            array_values(array_unique($blockers, SORT_REGULAR)),
            $event->rawSummary,
            $context,
        );
    }

    public function fromSnapshot(
        ConferenceRoomSnapshot $snapshot,
        ?ConferenceObservationTimestamp $observedAt = null,
        ?RuntimeObservationContext $context = null,
    ): ConferenceSnapshotObservation {
        return new ConferenceSnapshotObservation(
            $snapshot,
            $observedAt ?? ConferenceObservationTimestamp::now(),
            $snapshot->isComplete() ? ObservationConfidence::Observed : ObservationConfidence::Partial,
            $context,
        );
    }
}
