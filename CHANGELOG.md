# Changelog

## 0.1.0 - 2026-05-18

Initial framework-agnostic FreeSWITCH `mod_conference` protocol/domain package.

Stable 0.1.0 support includes:

- Immutable conference value objects and snapshots for FreeSWITCH conference facts.
- `conference::maintenance` `add-member` and `del-member` event parsing.
- Safe degradation for unknown conference maintenance actions.
- `conference <name> list` command text generation.
- `conference <name> list` reply parsing, including live-observed room-specific member rows without a conference header when the conference name is supplied by the caller.
- Joined, left, and snapshot observations with deterministic serialization.
- FreeSWITCH/mod_conference blocker vocabulary and outbound command argument safety.
- Boundary tests that keep the package framework-agnostic, transport-free, and downstream-lifecycle-free.

Evidence for this release includes sanitized live-captured fixtures from a local FreeSWITCH 1.10.11 Docker lab on 2026-05-18 for:

- `conference::maintenance` `add-member`
- `conference::maintenance` `del-member`
- `conference <name> list` member reply parsing
- rejected `conference <name> list` replies

Synthetic representative contract fixtures remain in place for negative and incomplete cases, including empty list replies, unparseable replies, unknown actions, missing conference names, and non-conference custom events.

The live validation suite is operator-run only and remains excluded from `composer check`.

Deferred or unsupported in 0.1.0:

- `json_list` and `xml_list` reply support.
- Conference member administration command families such as `bgdial`, `kick`, `mute`, `deaf`, recording, media, transfer, and relate operations.
- Runtime adapters, socket ownership, reconnect behavior, health management, replay envelope/storage helpers, framework-specific integrations, persistence adapters, and downstream lifecycle readiness decisions.
