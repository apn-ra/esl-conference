<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Event;

use Apntalk\EslConference\Model\ConferenceAction;
use Apntalk\EslConference\Model\ConferenceEventSubclass;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;

final readonly class ConferenceMaintenanceEventFactory
{
    public function __construct(
        private ConferenceEventFieldExtractor $extractor = new ConferenceEventFieldExtractor(),
        private ConferenceActionClassifier $classifier = new ConferenceActionClassifier(),
    ) {
    }

    /**
     * @param object|array<string, string> $event
     */
    public function parse(object|array $event): ConferenceEventParseResult
    {
        $headers = $this->extractor->extract($event);
        $subclass = ConferenceEventSubclass::fromString((string) ($this->extractor->get($headers, 'Event-Subclass') ?? ''));

        if ($subclass === null || ! $subclass->isConferenceMaintenance()) {
            return ConferenceEventParseResult::notConferenceMaintenance($headers, [ConferenceBlocker::ConferenceEventSubclassMismatch]);
        }

        $action = ConferenceAction::fromString((string) ($this->extractor->get($headers, 'Action') ?? ''));
        if ($action === null) {
            return ConferenceEventParseResult::incomplete($headers, [ConferenceBlocker::ConferenceActionMissing]);
        }

        $blockers = $this->blockersFor($headers);
        $kind = $this->classifier->classify($action);

        if ($kind === ConferenceAction::ADD_MEMBER) {
            $eventObject = $this->joinedEvent($action, $headers, $blockers);

            return $blockers === []
                ? ConferenceEventParseResult::recognized($eventObject)
                : ConferenceEventParseResult::incomplete($headers, $blockers, $eventObject);
        }

        if ($kind === ConferenceAction::DEL_MEMBER) {
            $eventObject = $this->leftEvent($action, $headers, $blockers);

            return $blockers === []
                ? ConferenceEventParseResult::recognized($eventObject)
                : ConferenceEventParseResult::incomplete($headers, $blockers, $eventObject);
        }

        $blockers[] = ConferenceBlocker::ConferenceActionUnknown;
        $eventObject = $this->unknownEvent($action, $headers, $blockers);

        return ConferenceEventParseResult::unknownAction($eventObject, $blockers);
    }

    /**
     * @param array<string, string> $headers
     * @param list<ConferenceBlocker> $blockers
     */
    private function joinedEvent(ConferenceAction $action, array $headers, array $blockers): ConferenceMemberJoinedEvent
    {
        $event = ConferenceMaintenanceEvent::fromHeaders($action, $headers, $this->extractor, $blockers);

        return new ConferenceMemberJoinedEvent($event->action, $event->conferenceName, $event->profile, $event->conferenceSize, $event->member, $event->rawSummary, $event->blockers);
    }

    /**
     * @param array<string, string> $headers
     * @param list<ConferenceBlocker> $blockers
     */
    private function leftEvent(ConferenceAction $action, array $headers, array $blockers): ConferenceMemberLeftEvent
    {
        $event = ConferenceMaintenanceEvent::fromHeaders($action, $headers, $this->extractor, $blockers);

        return new ConferenceMemberLeftEvent($event->action, $event->conferenceName, $event->profile, $event->conferenceSize, $event->member, $event->rawSummary, $event->blockers);
    }

    /**
     * @param array<string, string> $headers
     * @param list<ConferenceBlocker> $blockers
     */
    private function unknownEvent(ConferenceAction $action, array $headers, array $blockers): ConferenceUnknownMaintenanceAction
    {
        $event = ConferenceMaintenanceEvent::fromHeaders($action, $headers, $this->extractor, $blockers);

        return new ConferenceUnknownMaintenanceAction($event->action, $event->conferenceName, $event->profile, $event->conferenceSize, $event->member, $event->rawSummary, $event->blockers);
    }

    /**
     * @param array<string, string> $headers
     * @return list<ConferenceBlocker>
     */
    private function blockersFor(array $headers): array
    {
        $blockers = [];

        if (trim((string) ($this->extractor->get($headers, 'Conference-Name') ?? '')) === '') {
            $blockers[] = ConferenceBlocker::ConferenceNameMissing;
        }

        if (trim((string) ($this->extractor->get($headers, 'Member-ID') ?? '')) === '') {
            $blockers[] = ConferenceBlocker::ConferenceMemberIdMissing;
        }

        if (trim((string) ($this->extractor->get($headers, 'Unique-ID') ?? '')) === '') {
            $blockers[] = ConferenceBlocker::ConferenceChannelUuidMissing;
        }

        return $blockers;
    }
}
