# Blocker Vocabulary

| Blocker | Stable since | Meaning |
| --- | --- | --- |
| `conference_name_missing` | 0.1.0 | A required conference name was absent. |
| `conference_event_subclass_mismatch` | 0.1.0 | A custom event was not `conference::maintenance`. |
| `conference_action_missing` | 0.1.0 | A maintenance event had no action header. |
| `conference_action_unknown` | 0.1.0 | A maintenance action is not a stable 0.1.0 action. |
| `conference_member_id_missing` | 0.1.0 | A member-specific fact lacked a member id. |
| `conference_channel_uuid_missing` | 0.1.0 | A member-specific fact lacked a channel uuid. |
| `conference_reply_unparseable` | 0.1.0 | A command reply could not be parsed as a supported list reply. |
| `conference_reply_unsupported` | 0.1.0 | A command reply shape is outside the stable parser contract. |
| `conference_member_not_found` | 0.1.0 | A requested member was absent from known conference facts. |
| `conference_command_rejected` | 0.1.0 | FreeSWITCH rejected the command. |
| `conference_observation_incomplete` | 0.1.0 | An observation is usable but missing critical evidence. |
| `conference_snapshot_unsupported` | 0.1.0 | A snapshot source is outside the stable contract. |
| `conference_command_argument_unsafe` | 0.1.0 | An outbound command token failed closed. |
| `conference_vocabulary_unproven` | 0.1.0 | A requested vocabulary item lacks evidence for stable support. |
