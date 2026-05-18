# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Authoritative Sources

Before planning or editing, treat these as authoritative (in this order):

1. `docs/implementation-plan.md` — phased plan for 0.1.0 scope.
2. `AGENTS.md` — the operational contract: boundary rules, forbidden vocabulary, public API policy, release gate, fixture/provenance requirements.
3. `README.md`, `composer.json`, and the rest of `docs/`.

`AGENTS.md` is binding. Re-read it when changing scope, public API, vocabulary, or dependencies — it lists the forbidden terms, allowed/forbidden responsibilities, and the release gate checklist that this package must hold to.

## Common Commands

```bash
composer validate --strict   # composer.json sanity
composer cs-check            # php-cs-fixer --dry-run --diff
composer analyse             # phpstan (level 6, src + tests)
composer unit                # phpunit --testsuite Unit
composer contract            # phpunit --testsuite Contract
composer fixture             # phpunit --testsuite Fixture (runs tests/FixtureTest)
composer integration         # phpunit --testsuite Integration
composer boundary            # phpunit --testsuite Boundary
composer check               # cs-check + analyse + all of the above test suites
composer live-check          # phpunit --testsuite Live — operator-run only, NOT in composer check
```

Run a single test file or filter:

```bash
vendor/bin/phpunit tests/Unit/Path/To/SomeTest.php
vendor/bin/phpunit --filter testMethodName
```

When touching parser/command/observation code, run the focused suite (`unit`/`contract`/`fixture`) before `composer check`.

The `Fixture` composer script maps to the `tests/FixtureTest/` directory (note the suffix) — `tests/Fixture/` holds the raw `.event`/`.txt` fixture data those tests load.

## Live FreeSWITCH Lab

`docker/` contains a local FreeSWITCH Docker lab used only for live validation, fixture capture, and provenance updates. Default suites must not require it. Live runs are gated by env (see `.env.live.local` conventions in `AGENTS.md` §9) and are excluded from `composer check` and default CI. Bring it up/down with `docker compose up -d --build` / `docker compose down` from inside `docker/`.

## Architecture

This is a **pure protocol/domain library**, not a runtime. It answers "what did FreeSWITCH `mod_conference` say?" and nothing about application readiness, routing, persistence, sockets, or lifecycle.

PHP `^8.3`, PSR-4 namespace `Apntalk\EslConference\` under `src/`, tests under `Apntalk\EslConference\Tests\`. The only runtime dependency is `apntalk/esl-core`. No Laravel, no DB, no ReactPHP — boundary tests in `tests/Boundary/` enforce this.

Data flow (one direction, no I/O):

```text
normalized event headers  -> ConferenceMaintenanceEventFactory -> ConferenceEventParseResult -> Observation
command reply text         -> ConferenceListReplyParser        -> ConferenceCommandParseResult -> ConferenceRoomSnapshot / Observation
ConferenceName             -> ConferenceListMembersCommand     -> command text ("conference <name> list")
```

Module layout under `src/`:

- `Model/` — immutable value objects (`ConferenceName`, `ConferenceMemberId`, `ConferenceChannelUuid`, `ConferenceAction`, `ConferenceEventSubclass`, identities, etc.). Preserve original FreeSWITCH values; fail closed on invalid outbound values.
- `Event/` — `conference::maintenance` normalization. `ConferenceMaintenanceEventFactory` classifies headers via `ConferenceActionClassifier` + `ConferenceEventFieldExtractor` and returns `ConferenceMemberJoinedEvent` / `ConferenceMemberLeftEvent` / `ConferenceUnknownMaintenanceAction` wrapped in a `ConferenceEventParseResult`.
- `Command/` — deterministic command text only. `ConferenceListMembersCommand` produces `conference <name> list`. Never prepends `api`/`bgapi`, never executes anything. Unsafe outbound args throw `Exception\UnsafeConferenceCommandArgument`.
- `Parser/` — `ConferenceListReplyParser` + `ConferenceMemberListParser` with a `ParseMode` (lenient returns partial snapshots plus blockers; strict fails closed).
- `Reply/` + `Snapshot/` — `ConferenceCommandParseResult`, `ConferenceRoomSnapshot`, `ConferenceMemberSnapshot`.
- `Observation/` — neutral domain observations (`MemberJoinedObservation`, `MemberLeftObservation`, `MemberPresenceObservation`, `ConferenceSnapshotObservation`) carrying source, confidence, blockers, and redacted raw summary. **No** tenant/provider/binding/session/lifecycle IDs.
- `Vocabulary/ConferenceBlocker` — FreeSWITCH-specific blocker codes (see `docs/blocker-vocabulary.md`). Blockers are mod_conference-specific, never APNTalk lifecycle terms.

## Stable vs Provisional Surface (0.1.0)

Stable, fixture-backed: `conference::maintenance` `add-member` / `del-member`, unknown-action degradation, `conference <name> list` command + reply parsing, room/member snapshots, observations, blocker vocabulary, outbound argument safety. Live fixture evidence is from FreeSWITCH 1.10.11 in the local lab; empty/unparseable replies remain synthetic negative contract fixtures.

Out of scope for 0.1.0 (do **not** widen): `json_list`, `bgdial`, member administration commands (kick/mute/deaf/etc.), recording/playback/transfer/relate, live connection helpers, replay envelopes/storage, any framework integration. Adding any of these requires explicit plan changes and provenance evidence — don't slip them in opportunistically.

## Boundary Rules (Enforced by Tests)

`tests/Boundary/` will fail the build if any of these slip into `src/`:

- APNTalk vocabulary: `tenant`, `tenant_id`, `provider_binding`, `sip_account`, `campaign`, `lead`, `callReady`, `conferenceReady`, etc.
- Framework/runtime/persistence terms: `Laravel`, `Eloquent`, `database`, `worker`, `supervisor`, lifecycle-snapshot terms.
- Composer dependencies outside `php` + `apntalk/esl-core` (plus dev tooling).

The only docs allowed to mention forbidden terms are `docs/package-boundaries.md`, `docs/downstream-apntalk-integration.md`, and `docs/implementation-plan.md`, and only to state those concepts live **outside** this package. `docs/implementation-plan.md` is the authoritative scoping document and must enumerate the forbidden downstream/APNTalk surface so the package can prove it does not implement it.

## Outbound Command Safety

Outbound command construction must reject CR, LF, NUL, empty conference names, unbounded strings, and any separator that could alter FreeSWITCH command structure. Inbound observed FreeSWITCH values may preserve raw strings safely — the asymmetry is intentional. If dial strings are ever added, use a dedicated trusted/raw value object and mark it provisional until proven.

## Fixture / Provenance Discipline

Every stable command/event/reply claim must be backed by evidence recorded in `docs/freeswitch-vocabulary-provenance.md` (FreeSWITCH version, exact command, raw fixture path, fields observed/absent, sanitization notes, status). When capturing from the Docker lab, sanitize secrets and host-specific identifiers; never commit real passwords, SIP credentials, customer numbers, or tenant/provider/binding IDs. Release docs must not claim support for any vocabulary item without provenance evidence.

## Code Style

PSR-12, `declare(strict_types=1)` everywhere, short array syntax, alphabetized imports, single quotes. Enforced by `.php-cs-fixer.dist.php`. PHPStan level 6 over `src/` and `tests/`.
