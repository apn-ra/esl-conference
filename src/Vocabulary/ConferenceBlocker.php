<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Vocabulary;

enum ConferenceBlocker: string
{
    case ConferenceNameMissing = 'conference_name_missing';
    case ConferenceEventSubclassMismatch = 'conference_event_subclass_mismatch';
    case ConferenceActionMissing = 'conference_action_missing';
    case ConferenceActionUnknown = 'conference_action_unknown';
    case ConferenceMemberIdMissing = 'conference_member_id_missing';
    case ConferenceChannelUuidMissing = 'conference_channel_uuid_missing';
    case ConferenceReplyUnparseable = 'conference_reply_unparseable';
    case ConferenceReplyUnsupported = 'conference_reply_unsupported';
    case ConferenceMemberNotFound = 'conference_member_not_found';
    case ConferenceCommandRejected = 'conference_command_rejected';
    case ConferenceObservationIncomplete = 'conference_observation_incomplete';
    case ConferenceSnapshotUnsupported = 'conference_snapshot_unsupported';
    case ConferenceCommandArgumentUnsafe = 'conference_command_argument_unsafe';
    case ConferenceVocabularyUnproven = 'conference_vocabulary_unproven';
}
