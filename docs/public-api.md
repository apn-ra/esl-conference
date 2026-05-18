# Public API

## Stable in 0.1.0

- Domain value objects under `Apntalk\EslConference\Model`.
- `ConferenceMaintenanceEventFactory` for normalized `conference::maintenance` headers.
- Typed event wrappers for `add-member`, `del-member`, and unknown maintenance actions.
- `ConferenceListMembersCommand` for deterministic `conference <name> list` command text.
- `ConferenceListReplyParser` and `ConferenceMemberListParser` for fixture-backed list replies. Room-specific `conference <name> list` replies without a header require the caller to provide the conference name to the parser.
- Snapshot models for rooms and members.
- Observation models for member joins, member leaves, member presence, and snapshots.
- `ConferenceBlocker` for FreeSWITCH conference blockers.
- `docs/blocker-vocabulary.md` documents each stable blocker value.

Stable APIs are covered by contract fixtures and documented in `docs/freeswitch-vocabulary-provenance.md`. The bounded add-member, del-member, member list, and rejected list reply path has sanitized live fixture evidence from the local FreeSWITCH lab. Empty and unparseable reply fixtures remain synthetic negative contract fixtures.

### Observation `raw_summary`

`raw_summary` is a bounded diagnostic summary of selected FreeSWITCH event headers. It is not a full raw event dump and must not contain caller-identifying headers.

Stable 0.1.0 allowed keys:

- `Event-Subclass`
- `Action`
- `Conference-Name`
- `Conference-Profile-Name`
- `Conference-Size`
- `Member-ID`
- `Member-Type`
- `Channel-Name`
- `Unique-ID`

Downstream systems that need application-owned context must attach that context outside `esl-conference`.

## Unsupported in 0.1.0

- `json_list` parsing.
- `bgdial` and member administration command families.
- Recording, media playback, transfer, and relate commands.
- Live connection helpers.
- Replay envelope factories, replay storage, and replay artifact writers. `ObservationSource::Replay` is only a neutral source label.
- Framework integrations.
