# Event Vocabulary

## Stable Actions

| Item | Status | Blockers |
| --- | --- | --- |
| `conference::maintenance` `add-member` | stable 0.1.0 | `conference_name_missing`, `conference_member_id_missing`, `conference_channel_uuid_missing` |
| `conference::maintenance` `del-member` | stable 0.1.0 | `conference_name_missing`, `conference_member_id_missing`, `conference_channel_uuid_missing` |
| unknown maintenance action | stable safe degradation | `conference_action_unknown` |

## Required Classification Headers

- `Event-Subclass`
- `Action`

## Supported Event Headers

- `Conference-Name`
- `Conference-Profile-Name`
- `Conference-Size`
- `Member-ID`
- `Member-Type`
- `Channel-Name`
- `Unique-ID`
- `Caller-Caller-ID-Number`
- `Caller-Caller-ID-Name`
- `Caller-Destination-Number`
- `Caller-Username`
- `Caller-Context`

Missing optional headers are tolerated. Missing critical headers produce blocker-bearing parse results.
