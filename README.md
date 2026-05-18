# apntalk/esl-conference

Framework-agnostic FreeSWITCH `mod_conference` domain primitives for PHP ESL integrations.

This package answers what FreeSWITCH `mod_conference` command, event, and reply data said. It does not own application readiness, routing, storage, sockets, reconnects, or process control.

## Stable 0.1.0 Scope

- `conference::maintenance` event normalization for `add-member` and `del-member`.
- Unknown maintenance action handling with explicit blockers.
- `conference <name> list` command text generation.
- `conference <name> list` reply parsing into room and member snapshots.
- Conference observations derived from events and command replies.
- FreeSWITCH-specific blocker vocabulary.
- Sanitized live fixture coverage for the bounded event/list/rejected reply path, with synthetic contract fixtures retained for negative parser cases.
- Negative boundary tests for framework and runtime creep.

## Deferred Scope

The first release intentionally does not expose stable support for `json_list`, `bgdial`, member administration command families, recording commands, live connection helpers, replay artifact writers, or framework integrations.

## Verification

```bash
composer validate --strict
composer check
```

Live FreeSWITCH validation is operator-run only:

```bash
composer live-check
```
