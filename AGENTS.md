# AGENTS.md — `apntalk/esl-conference`

This file is the operational contract for coding agents working in this repository.

`apntalk/esl-conference` is a generic, framework-agnostic FreeSWITCH `mod_conference` protocol/domain package. It must remain small, fixture-proven, and boundary-safe.

The package answers:

```text
What did FreeSWITCH mod_conference command/event/reply say?
```

It must not answer:

```text
Does this tenant/account/binding/session become conferenceReady or callReady?
```

APNTalk or another downstream application owns business readiness, provider selection, persistence, routing, and lifecycle authority.

---

## 1. Source of truth

Before planning or editing, read:

```text
docs/implementation-plan.md
README.md
composer.json
```

If `docs/implementation-plan.md` exists, treat it as authoritative.

For `0.1.0`, keep the implementation deliberately narrow:

```text
conference::maintenance join/leave events
unknown maintenance action handling
conference <name> list command generation
conference list reply parsing
conference room/member snapshots
conference observations
blocker vocabulary
command argument safety
fixture-backed FreeSWITCH vocabulary provenance
negative boundary tests
```

Do not widen the package because a downstream APNTalk integration wants convenience behavior. Keep the package generic.

---

## 2. Package boundary

Allowed responsibilities:

```text
FreeSWITCH mod_conference command text generation
FreeSWITCH conference event normalization
FreeSWITCH conference reply parsing
conference member identity models
conference snapshots
conference observations
conference command/reply parse results
conference blocker/error vocabulary
fixture-backed FreeSWITCH conference vocabulary provenance
```

Forbidden responsibilities:

```text
tenant/account/provider-binding/session lifecycle authority
conferenceReady/callReady decisions
provider-node selection
multi-PBX routing policy
Laravel service providers
database persistence
worker supervision
socket ownership
reconnect loops
runtime health model
replay storage
```

Base package dependencies may include:

```text
php
apntalk/esl-core
dev tools such as phpunit/phpstan/php-cs-fixer
```

Base package dependencies must not include:

```text
Laravel
Eloquent
database clients
apntalk/esl-react
apntalk/esl-replay
ReactPHP event loop packages
runtime supervisor packages
```

Optional integrations may be added later only when the implementation plan explicitly allows them.

---

## 3. Forbidden vocabulary in source/public API

Do not introduce these terms in `src/`, public class names, public method names, stable vocabulary, or stable package API:

```text
tenant
tenant_id
provider_binding
providerBinding
provider_binding_id
sip_account
sipAccount
campaign
lead
callReady
conferenceReady
Laravel
Eloquent
database
worker
supervisor
lifecycle snapshot
canonical lifecycle
```

Allowed exception docs:

```text
docs/package-boundaries.md
docs/downstream-apntalk-integration.md
docs/implementation-plan.md
```

Those docs may mention forbidden terms only to state that those concepts are owned outside `esl-conference`.
`docs/implementation-plan.md` is the authoritative scoping document and must enumerate the forbidden downstream/APNTalk surface so the package can prove it does not implement it.

---

## 4. Public API policy

The package must distinguish:

```text
Stable public API
  Fixture-backed, documented, and protected by tests.

Provisional public API
  Explicitly experimental and excluded from release-readiness claims.

Internal implementation details
  May change freely before 1.0.
```

Do not expose a stable command, parser, event action, or reply shape unless it is backed by at least one of:

```text
sanitized real FreeSWITCH fixture
live-lab validation evidence in docs/freeswitch-vocabulary-provenance.md
explicit provisional status excluding it from stable release claims
```

For `0.1.0`, stable claims must stay focused on:

```text
conference::maintenance add-member
conference::maintenance del-member
conference <name> list
conference list reply parser
room/member snapshots
observations derived from events/snapshots
safe unknown-action degradation
stable blocker vocabulary
```

Do not claim stable support for:

```text
json_list
xml_list unless fixture/live-proven
bgdial
kick
mute/unmute
deaf/undeaf
recording commands
play/say/transfer/relate commands
runtime helpers
replay envelopes
```

---

## 5. Implementation phases

Work phase-by-phase. Do not mix phases unless the user explicitly asks and the change remains small.

### Phase 0 — repository baseline

Expected outputs:

```text
composer.json
phpunit/phpstan/php-cs-fixer config
GitHub Actions if appropriate
README.md
docs/public-api.md
docs/architecture.md
docs/package-boundaries.md
docs/downstream-apntalk-integration.md
docs/freeswitch-vocabulary-provenance.md
```

Exit criteria:

```text
composer validate --strict passes
composer check passes if configured
no framework/runtime/database dependency
public boundaries documented
provenance document exists
```

### Phase 1 — domain model

Stable value objects/models may include:

```text
ConferenceName
ConferenceProfile
ConferenceMemberId
ConferenceChannelUuid
ConferenceAction
ConferenceEventSubclass
ConferenceObservationTimestamp
ConferenceCommandToken
ConferenceMemberSelector
ConferenceMemberIdentity
ConferenceMemberState
ConferenceRoomSnapshot
ConferenceMemberSnapshot
ConferenceChannelIdentity
ConferenceCallerIdentity
```

Rules:

```text
immutable objects
preserve original FreeSWITCH values
parse only conservative candidates
support unknown/missing optional fields
fail closed for invalid outbound values
deterministic toArray() where implemented
no APNTalk IDs
```

### Phase 2 — event normalization

Stable event scope:

```text
ConferenceMaintenanceEvent
ConferenceMaintenanceEventFactory
ConferenceEventParseResult
ConferenceActionClassifier
ConferenceEventFieldExtractor
ConferenceMemberJoinedEvent
ConferenceMemberLeftEvent
ConferenceUnknownMaintenanceAction
```

Stable actions:

```text
add-member
del-member
```

Required behavior:

```text
unknown actions produce blocker-bearing unknown results
non-conference custom events are notConferenceMaintenance
missing critical fields produce incomplete/blocker results
missing optional headers are tolerated
```

Expected fixtures:

```text
tests/Fixture/events/conference_member_joined.event
tests/Fixture/events/conference_member_left.event
tests/Fixture/events/conference_unknown_action.event
tests/Fixture/events/conference_missing_name.event
tests/Fixture/events/non_conference_custom_event.event
```

### Phase 3 — command builders

Stable command scope:

```text
ConferenceCommand
ConferenceListMembersCommand
```

Expected command text:

```text
conference <name> list
```

Rules:

```text
produce deterministic command text
do not execute commands
do not prepend api/bgapi
do not know sockets/promises/runtime/APNTalk/Laravel/DB
reject unsafe outbound arguments
```

### Phase 4 — reply parsing and snapshots

Stable reply/snapshot scope:

```text
ConferenceListReplyParser
ConferenceMemberListParser
ConferenceCommandParseResult
ConferenceRoomSnapshot
ConferenceMemberSnapshot
ParseMode
```

Expected fixtures:

```text
tests/Fixture/replies/conference_list_empty.txt
tests/Fixture/replies/conference_list_members.txt
tests/Fixture/replies/conference_unparseable.txt
tests/Fixture/replies/conference_rejected.txt
```

Rules:

```text
lenient mode returns partial snapshots plus blockers where possible
strict mode fails closed on missing critical fields or unsupported formats
unparseable/rejected replies produce stable blocker-bearing results
```

### Phase 5 — observations

Stable observation scope:

```text
ConferenceObservation
MemberPresenceObservation
MemberJoinedObservation
MemberLeftObservation
ConferenceSnapshotObservation
ConferenceObservationFactory
ObservationSource
ObservationConfidence
RuntimeObservationContext
```

Observation payloads include:

```text
conference name
member id if present
channel uuid if present
action/state
observed at
source type
confidence
blockers
redacted raw field summary
generic source metadata
```

Observation payloads must not include:

```text
tenant id
provider binding id
sip account id
campaign id
lead id
APNTalk session id
conferenceReady/callReady decision
```

### Phase 6 — boundary tests

Expected tests:

```text
NoApntalkVocabularyInSourceTest
NoFrameworkDependencyTest
NoRuntimeOwnershipVocabularyTest
ComposerDependencyBoundaryTest
```

These tests should fail if source/API dependencies or vocabulary violate the package boundary.

---

## 6. Command argument safety

Outbound command construction must fail closed.

Reject:

```text
CR
LF
NUL bytes
empty conference names
unbounded strings
command separators that can alter FreeSWITCH command structure
```

Important distinction:

```text
Inbound observed FreeSWITCH values may preserve raw strings safely.
Outbound command arguments must be validated before command text generation.
```

Do not over-sanitize future dial strings casually. FreeSWITCH dial strings may legitimately contain braces, commas, equals signs, slashes, colons, SIP params, and variables. If dial strings are added later, use a separate trusted/raw dial-string value object and mark the API provisional unless proven.

---

## 7. Blocker vocabulary

Blockers must remain FreeSWITCH/mod_conference-specific, not APNTalk lifecycle blockers.

Expected stable terms may include:

```text
conference_name_missing
conference_event_subclass_mismatch
conference_action_missing
conference_action_unknown
conference_member_id_missing
conference_channel_uuid_missing
conference_reply_unparseable
conference_reply_unsupported
conference_member_not_found
conference_command_rejected
conference_observation_incomplete
conference_snapshot_unsupported
conference_command_argument_unsafe
conference_vocabulary_unproven
```

Document every stable blocker in:

```text
docs/event-vocabulary.md
docs/command-vocabulary.md
docs/reply-formats.md
```

as appropriate.

---

## 8. Fixture and provenance requirements

Maintain:

```text
docs/freeswitch-vocabulary-provenance.md
```

Every stable command/event/reply claim must have evidence.

For each vocabulary item, record:

```text
FreeSWITCH version tested
mod_conference command or event action tested
exact command string if applicable
raw fixture path
parsed model produced
fields observed
fields absent
unsupported/version-specific fields
live validation date if applicable
environment notes
sanitization notes
stable/provisional/unsupported status
```

Release-facing docs must not claim support for an event action, command, or reply shape unless this provenance document contains evidence.

---

## 9. Local FreeSWITCH live testing

Default test suites must not require live FreeSWITCH.

Live validation is optional, operator-run, and excluded from default CI.

This repository uses a local FreeSWITCH Docker lab located in:

```text
docker/
```

Use it only for live validation, fixture capture, and provenance updates.

### 9.1 Start local FreeSWITCH

From the repository root:

```bash
cd docker
docker compose up -d --build
docker compose ps
cd ..
```

If the local compose file already builds the image, keep using that. Do not move the Docker lab out of `docker/` without updating this file and `docs/live-validation.md`.

### 9.2 Verify FreeSWITCH is reachable

Common local defaults:

```text
ESL host: 127.0.0.1
ESL port: 8021
ESL password: local/dev secret from docker config
```

Do not commit real secrets.

If the Docker lab uses the standard development password, keep it in local-only env files and examples, not in release claims.

Suggested local env file:

```text
.env.live.local
```

Suggested variables:

```bash
ESL_CONFERENCE_LIVE_TEST=1
ESL_CONFERENCE_LIVE_HOST=127.0.0.1
ESL_CONFERENCE_LIVE_PORT=8021
ESL_CONFERENCE_LIVE_PASSWORD=change-me-local-only
ESL_CONFERENCE_LIVE_CONFERENCE=support-1001@default
```

Real process environment must override `.env.live.local`.

### 9.3 Run live validation

Only run live tests when explicitly requested by the user/operator.

Expected command:

```bash
composer live-check
```

If the suite does not exist yet, create it as an operator-run test suite only. Do not include it in `composer check`.

Live validation should prove only package facts:

```text
subscribe to conference::maintenance
join a conference
observe add-member
parse typed joined event
convert to MemberJoinedObservation
run conference <name> list
parse reply into ConferenceRoomSnapshot
observe del-member
parse typed left event
convert to MemberLeftObservation
record sanitized fixtures
update docs/freeswitch-vocabulary-provenance.md
```

Live validation must not prove APNTalk lifecycle closure. APNTalk separately proves:

```text
FreeSWITCH observation -> canonical lifecycle conferenceReady/callReady
```

### 9.4 Fixture capture rules

When capturing from the Docker lab:

```text
sanitize secrets
sanitize host-specific identifiers where needed
preserve FreeSWITCH field names and representative values
record source command/event and FreeSWITCH version
store fixtures under tests/Fixture/
update docs/freeswitch-vocabulary-provenance.md
```

Do not store:

```text
real passwords
production SIP credentials
customer phone numbers
tenant IDs
provider binding IDs
database records
```

### 9.5 Stop local FreeSWITCH

From the repository root:

```bash
cd docker
docker compose down
cd ..
```

Use `docker compose down -v` only when the operator explicitly wants volumes removed.

---

## 10. Test commands

Prefer these commands:

```bash
composer validate --strict
composer cs-check
composer analyse
composer unit
composer contract
composer fixture
composer integration
composer boundary
composer check
```

Live tests:

```bash
composer live-check
```

Rules:

```text
composer check must not require live FreeSWITCH
composer live-check must be gated by env and operator intent
CI must not require live FreeSWITCH unless a dedicated manual workflow is added
```

When touching parser/command/observation code, run the focused suite first, then broader checks.

If a command cannot run because dependencies are missing or the sandbox has no network, report that honestly and state which command was not run.

---

## 11. Documentation rules

Keep these docs aligned with code and tests:

```text
README.md
CHANGELOG.md
docs/public-api.md
docs/architecture.md
docs/package-boundaries.md
docs/event-vocabulary.md
docs/command-vocabulary.md
docs/reply-formats.md
docs/freeswitch-vocabulary-provenance.md
docs/downstream-apntalk-integration.md
docs/live-validation.md
```

Docs must distinguish:

```text
stable
provisional
unsupported/deferred
```

Do not let README or release notes overclaim unproven behavior.

---

## 12. Release gate

Before declaring `0.1.0` ready, verify:

```text
composer check passes
conference::maintenance add-member/del-member events parse from fixtures
unknown event actions degrade safely
missing critical event fields produce blocker-bearing parse results
conference list replies parse into snapshots
unparseable replies fail closed with blockers
command builders generate deterministic mod_conference command text
unsafe outbound command arguments fail closed
observation objects are immutable and serializable
partial observations include explicit blockers
no APNTalk concepts exist in source/public API
no Laravel dependency exists
no DB or runtime connection ownership exists
package docs clearly define boundaries
FreeSWITCH vocabulary provenance exists for every stable command/event/reply claim
negative-boundary tests pass
```

Do not tag/release if stable claims depend on unproven `json_list`, runtime helpers, replay envelopes, or APNTalk lifecycle behavior.

---

## 13. Preferred agent workflow

When using Codex skills, prefer:

```text
$esl-conference-plan-slicer
$esl-conference-boundary-guard
$esl-conference-fixture-provenance
$esl-conference-command-safety
$esl-conference-parser-observation
$esl-conference-release-gate
```

When using Codex custom agents, prefer:

```text
conference_architect
conference_domain_builder
conference_event_normalizer
conference_command_guardian
conference_reply_snapshotter
conference_observation_mapper
conference_boundary_auditor
conference_provenance_auditor
conference_release_reviewer
```

Use read-only auditors for boundary/provenance/release checks. Use write-capable implementation agents only for the specific phase being implemented.

Do not let agents recursively spawn more agents unless explicitly requested.

---

## 14. Required final response from coding agents

Every implementation response must include:

```text
phase/scope completed
files changed
public API impact
stable/provisional/deferred status
tests run
commands not run and why
boundary/provenance impact
recommended next step
```

Every audit response must include:

```text
verdict
evidence checked
violations or gaps
minimal corrective action
tests or checks run
next recommended prompt
```
