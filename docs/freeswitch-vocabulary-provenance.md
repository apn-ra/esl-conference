# FreeSWITCH Vocabulary Provenance

This document records stable, provisional, and unsupported FreeSWITCH `mod_conference` vocabulary claims.

The bounded 0.1.0 live validation was run against the local Docker lab on 2026-05-18. FreeSWITCH reported:

```text
FreeSWITCH Version 1.10.11-release+git~20231222T180831Z~f24064f7c9~64bit (git f24064f 2023-12-22 18:08:31Z 64bit)
```

`module_exists mod_conference` returned `true`. Captured live fixtures were sanitized before commit. The live conference name `live-015` was replaced with `support-1001`, generated UUIDs were replaced with fixed example UUIDs, and loopback caller values were normalized to local example values. FreeSWITCH header names, action names, command/reply status text, and member-list row structure were preserved.

| Vocabulary item | Type | Status | Stable since | Evidence | Command or action | Fixture | Parsed model | Observed fields | Fields absent or not claimed | FreeSWITCH version | Live validation date | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `conference::maintenance add-member` | event action | stable live-validated | 0.1.0 | live-captured sanitized fixture | `Event-Subclass: conference::maintenance`, `Action: add-member`; setup used `conference live-015 dial loopback/9196 1001 'Support 1001'` | `tests/Fixture/events/conference_member_joined.event` | `ConferenceMemberJoinedEvent`, `MemberJoinedObservation` | `Event-Name`, `Event-Subclass`, `Action`, `Conference-Name`, `Conference-Profile-Name`, `Conference-Size`, `Member-ID`, `Member-Type`, `Channel-Name`, `Unique-ID`, caller headers | Version-specific headers outside the fixture are not part of the stable claim. | 1.10.11-release+git f24064f | 2026-05-18 | Preserves raw FreeSWITCH values after sanitization. |
| `conference::maintenance del-member` | event action | stable live-validated | 0.1.0 | live-captured sanitized fixture | `Event-Subclass: conference::maintenance`, `Action: del-member`; cleanup used `conference live-015 hup all` | `tests/Fixture/events/conference_member_left.event` | `ConferenceMemberLeftEvent`, `MemberLeftObservation` | `Event-Name`, `Event-Subclass`, `Action`, `Conference-Name`, `Conference-Profile-Name`, `Conference-Size`, `Member-ID`, `Member-Type`, `Channel-Name`, `Unique-ID`, caller headers | Version-specific headers outside the fixture are not part of the stable claim. | 1.10.11-release+git f24064f | 2026-05-18 | Preserves raw FreeSWITCH values after sanitization. |
| `conference <name> list` member reply | command/reply | stable live-validated | 0.1.0 | live-captured sanitized fixture | `conference live-015 list` | `tests/Fixture/replies/conference_list_members.txt` | `ConferenceRoomSnapshot`, `ConferenceMemberSnapshot`, `ConferenceSnapshotObservation` | member id, channel name, channel uuid, caller name, caller number, flags, additional trailing numeric fields | The room-specific reply did not include a conference header or reported member count in this lab; callers provide the conference name to the parser for this shape. The trailing numeric fields are tolerated but not interpreted as stable fields. | 1.10.11-release+git f24064f | 2026-05-18 | Parser also retains support for fixture-backed header shapes. |
| rejected `conference <name> list` reply | reply | stable live-validated | 0.1.0 | live-captured sanitized fixture | `conference live-015-missing list` | `tests/Fixture/replies/conference_rejected.txt` | blocker-bearing command parse result | `-ERR Conference <name> not found` status text | No snapshot is produced. | 1.10.11-release+git f24064f | 2026-05-18 | Produces `conference_command_rejected`. |
| empty `conference <name> list` reply | reply | stable contract | 0.1.0 | synthetic representative fixture | `conference support-1001@default list` | `tests/Fixture/replies/conference_list_empty.txt` | `ConferenceRoomSnapshot` | conference name and zero member count in representative header shape | Not live-captured. In this lab, a missing conference returned `-ERR` rather than an empty list. | not live-validated | pending | Retained as a parser contract for an empty supported list shape. |
| unparseable conference reply | reply | stable contract | 0.1.0 | synthetic representative negative fixture | `conference support-1001@default list` | `tests/Fixture/replies/conference_unparseable.txt` | blocker-bearing command parse result | raw summary only | Not a live FreeSWITCH shape. | not live-validated | pending | Produces `conference_reply_unparseable` or `conference_reply_unsupported`. |
| unknown `conference::maintenance` action | event action | stable safe degradation | 0.1.0 | synthetic representative fixture | `Event-Subclass: conference::maintenance`, unknown `Action` | `tests/Fixture/events/conference_unknown_action.event` | `ConferenceUnknownMaintenanceAction` | subclass, unknown action, representative conference fields | Not live-captured. | not live-validated | pending | Produces `conference_action_unknown`. |
| incomplete `conference::maintenance` event | event action | stable safe degradation | 0.1.0 | synthetic representative fixture | missing critical conference name | `tests/Fixture/events/conference_missing_name.event` | blocker-bearing event parse result | subclass, action, member/channel fields | Missing `Conference-Name` by design. | not live-validated | pending | Produces `conference_name_missing`. |
| non-conference custom event | event classification | stable safe degradation | 0.1.0 | synthetic representative fixture | non-matching `Event-Subclass` | `tests/Fixture/events/non_conference_custom_event.event` | blocker-bearing event parse result | non-conference custom event headers | Not live-captured. | not live-validated | pending | Produces `conference_event_subclass_mismatch`. |
| `conference <name> json_list` | command/reply | unsupported | n/a | pending | n/a | n/a | n/a | n/a | n/a | n/a | n/a | Not part of stable 0.1.0. |
| member administration command families | command | unsupported | n/a | pending | n/a | n/a | n/a | n/a | n/a | n/a | n/a | Not part of stable 0.1.0. |

## Fixture Notes

- Module: `mod_conference`.
- Live lab: repository-local Docker lab under `docker/`.
- Live evidence command:

```bash
TMPDIR=.tmp ESL_CONFERENCE_LIVE_TEST=1 ESL_CONFERENCE_CAPTURE_FIXTURES=1 ESL_CONFERENCE_LIVE_HOST=::1 ESL_CONFERENCE_LIVE_PORT=8021 ESL_CONFERENCE_LIVE_PASSWORD=<local-dev-password> ESL_CONFERENCE_LIVE_CONFERENCE=live-015 composer live-check
```

- Sanitization: live conference name replaced with `support-1001`; channel UUID replaced with `11111111-1111-4111-8111-111111111111`; caller values normalized to loopback example values; the local development ESL password is not part of fixture evidence; no committed production secret or host-specific identifier is required to parse the fixtures.
- Unsupported or version-specific fields outside the committed fixtures are intentionally not part of the stable 0.1.0 claim.
- Live validation remains optional and operator-run. `composer check` does not run the live suite.
