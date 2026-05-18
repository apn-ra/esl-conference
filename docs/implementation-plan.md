# Implementation Plan — `apntalk/esl-conference` Revised

## 0. Revision Goals

This revised plan keeps the original strategic boundary intact while tightening the first release around fixture-proven FreeSWITCH `mod_conference` behavior.

The main changes from the earlier plan are:

- keep `apntalk/esl-conference` generic, framework-agnostic, and FreeSWITCH-focused;
- narrow `0.1.0` to the APNTalk unlock path: conference maintenance join/leave events, list/snapshot parsing, observations, blockers, and boundary docs;
- treat `json_list`, expanded admin commands, replay envelopes, and live runtime helpers as later or provisional unless proven against real FreeSWITCH fixtures;
- add a FreeSWITCH vocabulary provenance document so every supported event/action/command/reply claim is backed by fixture or live-lab evidence;
- add negative-boundary tests to prevent APNTalk/Laravel/database/lifecycle vocabulary from leaking into the package;
- make command argument safety explicit and fail-closed.

---

## 1. Strategic Intent

Create `apntalk/esl-conference` as a **generic, framework-agnostic FreeSWITCH `mod_conference` protocol/domain package**.
Its purpose is **not** to make APNTalk pass a lifecycle requirement. Its purpose is to provide a durable, reusable conference layer for any PHP system that consumes FreeSWITCH ESL events and commands.

The package owns only FreeSWITCH conference primitives:

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

The package must not own APNTalk concepts:

```text
tenant_id
sip_account_id
provider_binding_id
campaign/session policy
conferenceReady
callReady
provider selection
multi-PBX routing policy
database persistence
Laravel models
worker supervision
```

That separation fits the existing package stack:

- `apntalk/esl-core` owns the framework-agnostic ESL wire model, event parsing substrate, typed protocol primitives, command/reply handling foundations, correlation primitives, and replay-aware lower-level protocol foundations.
- `apntalk/esl-react` owns live async runtime behavior: connection lifecycle, command dispatch, event streaming, reconnect supervision, and health.
- `apntalk/esl-replay` owns durable replay/evidence over stored artifacts, not live recovery or reconnect APIs.
- `apntalk/esl-conference` owns reusable FreeSWITCH `mod_conference` facts and transformations.

FreeSWITCH `mod_conference` is the provider feature to model. The central event source is the FreeSWITCH `conference::maintenance` custom event subclass. The central command/reply path for the first release is the FreeSWITCH `conference <name> list` command and its reply.

The package should answer:

```text
What did FreeSWITCH mod_conference command/event/reply say?
```

APNTalk should answer:

```text
Does that FreeSWITCH fact make this tenant/account/binding/session conferenceReady or callReady?
```

---

## 2. Package Position in the Stack

Target stack:

```text
APNTalk
  Owns tenant/account/provider-binding/session/lifecycle authority
  Owns conferenceReady/callReady decisions
  Owns multi-PBX policy

apntalk/laravel-freeswitch-esl or APNTalk integration layer
  Owns Laravel container wiring
  Owns provider-node credential resolution
  Owns DB-backed PBX registry and worker deployment

apntalk/esl-react
  Owns live ESL runtime, async commands, event streaming, reconnect/liveness

apntalk/esl-conference
  Owns generic FreeSWITCH mod_conference protocol/domain primitives

apntalk/esl-core
  Owns wire model, event parsing substrate, typed command/reply/event foundations

apntalk/esl-replay
  Owns durable replay/evidence of emitted artifacts
```

Composer target:

```json
{
  "name": "apntalk/esl-conference",
  "type": "library",
  "description": "Framework-agnostic FreeSWITCH mod_conference domain primitives for PHP ESL integrations.",
  "require": {
    "php": "^8.3",
    "apntalk/esl-core": "^0.2"
  },
  "autoload": {
    "psr-4": {
      "Apntalk\\EslConference\\": "src/"
    }
  }
}
```

Dependency rules:

```text
Required:
  apntalk/esl-core

Not required in the base package:
  apntalk/esl-react
  apntalk/esl-replay
  Laravel
  ReactPHP event loop packages unless required only by optional integration packages later
  database clients
```

`esl-conference` may eventually provide optional adapter helpers for `esl-react`, but the core library must be useful without a live runtime. It must be testable from raw normalized ESL events, command replies, and sanitized fixtures.

---

## 3. Non-Negotiable Boundaries

### 3.1 The Package Must Be Generic

No APNTalk lifecycle terms in source code, public API, model names, or stable package vocabulary:

```text
conferenceReady
callReady
tenant
campaign
lead
agent
provider_binding
sip_account
lifecycle snapshot
canonical lifecycle
```

Exception: boundary documentation may mention these terms only to state that APNTalk owns them and `esl-conference` does not.

### 3.2 The Package Must Be Connection-Scoped, Not Global

It must never assume one FreeSWITCH instance.

Every command/event/reply/observation object must be usable by a caller that attaches its own context externally:

```php
$observation = $conferenceEvent->toObservation();

$apntalkContext = new ProviderRuntimeContext(
    nodeKey: 'fs-node-1',
    bindingId: '...',
    sessionId: '...'
);

// APNTalk owns this mapping outside esl-conference.
$gateway->handleFreeSwitchConferenceObservation($apntalkContext, $observation);
```

The package may expose neutral correlation fields:

```text
source connection id
FreeSWITCH core uuid
event uuid
conference name
member id
channel uuid
caller username
received at
event sequence
correlation id
```

The package must not expose APNTalk-specific identity fields:

```text
tenantId
providerBindingId
sipAccountId
campaignId
leadId
session lifecycle id
```

### 3.3 The Package Must Not Open Sockets

No connection ownership.

No reconnect loops.

No supervisor.

No Laravel service provider.

No DB tables.

No event-loop requirement in the base package.

No runtime health model.

`esl-react` owns live runtime responsibilities like connection lifecycle, async command dispatch, event streaming, reconnect supervision, and health.

### 3.4 The Package Must Not Decide Business Readiness

It should say:

```text
member joined conference X
member left conference X
conference list reply says member Y exists
conference maintenance action was unknown but retained
conference snapshot was partial because field Y was missing
```

It should not say:

```text
agent is conferenceReady
agent is callReady
tenant call session is active
provider binding is healthy
APNTalk lifecycle can advance
```

APNTalk maps FreeSWITCH facts to lifecycle facts.

---

## 4. Public Scope Policy

The package must distinguish between:

```text
Stable public API
  Fixture-backed, documented, BC-policy protected.

Provisional public API
  Documented as experimental, not required for APNTalk unlock, and not included in stable release claims.

Internal implementation details
  May change freely before 1.0.
```

For `0.1.0`, stable public API should be small and proof-backed.

Do not expose a stable command, parser, or event action unless at least one of the following is true:

```text
- it has sanitized fixture coverage from a real FreeSWITCH source;
- it has live-lab validation evidence recorded in docs/freeswitch-vocabulary-provenance.md;
- it is explicitly marked provisional and excluded from release readiness claims.
```

The `json_list` command/parser must not be part of stable `0.1.0` unless the repository contains fixture or live-lab proof for the exact FreeSWITCH command and reply format. Prefer `list` first, and consider `xml_list` only if fixture-proven.

---

## 5. Package Modules

Recommended structure:

```text
src/
  Command/
  Reply/
  Event/
  Model/
  Parser/
  Snapshot/
  Observation/
  Vocabulary/
  Exception/
  Provenance/
  Integration/
    React/              # later/provisional only, not part of base 0.1.0 unlock

tests/
  Unit/
  Contract/
  Fixture/
  Integration/
  Boundary/
  Live/                 # operator-run only, excluded from default CI

docs/
  public-api.md
  architecture.md
  package-boundaries.md
  event-vocabulary.md
  command-vocabulary.md
  reply-formats.md
  freeswitch-vocabulary-provenance.md
  downstream-apntalk-integration.md
  live-validation.md
```

`Integration/React` should exist only when optional runtime helpers are introduced. It should not be required for the first APNTalk unlock release.

---

## 6. Public API Design

## 6.1 Commands

Namespace:

```php
Apntalk\EslConference\Command
```

Command builders produce deterministic command text compatible with ESL `api()` and `bgapi()` callers. They do not execute commands and do not prepend `api` or `bgapi` transport wrappers.

Preferred command interface:

```php
interface ConferenceCommand
{
    public function toCommandText(): string;

    public function commandName(): string;
}
```

Optional convenience methods may exist, but must remain transport-neutral:

```php
public function toApiCommand(): string;   // returns command body, e.g. "conference room list"
public function toBgApiCommand(): string; // returns command body suitable for bgapi, not an executed command
```

### Stable `0.1.0` command builders

Only include commands needed to unlock APNTalk parsing/integration:

```text
ConferenceListMembersCommand
```

Example:

```php
$command = ConferenceListMembersCommand::forConference(
    ConferenceName::fromString('support-1001@default')
);

$commandText = $command->toCommandText();
// conference support-1001@default list
```

### Optional/proven command builders

May be included in `0.1.x` only if fixture/live-proven:

```text
ConferenceXmlListCommand
```

### Deferred command builders

Move these to `0.2.0` or later:

```text
ConferenceBgdialCommand
ConferenceKickMemberCommand
ConferenceMuteMemberCommand
ConferenceUnmuteMemberCommand
ConferenceDeafMemberCommand
ConferenceUndeafMemberCommand
ConferenceRecordStartCommand
ConferenceRecordStopCommand
ConferencePlayCommand
ConferenceSayCommand
ConferenceTransferMemberCommand
ConferenceRelateMembersCommand
```

### Provisional command builders

Do not expose as stable until proven:

```text
ConferenceJsonListCommand
```

If implemented before proof exists, it must live behind explicit provisional naming/docs and must not be required by APNTalk integration.

### Command argument safety

Command construction must fail closed for unsafe outbound arguments.

Introduce explicit value objects as needed:

```text
ConferenceCommandToken
ConferenceName
ConferenceMemberSelector
ConferenceDialString
ConferenceFilePath
ConferenceTextArgument
```

Safety rules:

```text
- reject CR/LF;
- reject NUL bytes;
- reject empty conference names;
- reject unbounded string lengths;
- reject command separators if they can alter FreeSWITCH command structure;
- preserve raw FreeSWITCH values only after they are observed inbound, not when constructing outbound commands;
- avoid over-sanitizing dial strings, because valid FreeSWITCH dial strings may contain braces, commas, equals signs, slashes, colons, SIP params, and variables;
- separate trusted/raw dial-string construction from ordinary safe command-token construction.
```

Command builders must not know:

```text
connection runtime
Promise implementation
APNTalk context
Laravel container
provider node routing
DB persistence
```

---

## 6.2 Replies

Namespace:

```php
Apntalk\EslConference\Reply
```

Reply parsers convert raw ESL command replies into typed result objects.

Stable `0.1.0` reply scope:

```text
ConferenceListReplyParser
ConferenceMemberListParser
ConferenceRoomSnapshot
ConferenceMemberSnapshot
ConferenceCommandParseResult
```

Optional/proven reply scope:

```text
ConferenceXmlListReplyParser
```

Deferred/provisional reply scope:

```text
ConferenceJsonListReplyParser
ConferenceJsonListReply
```

Reply formats may vary by FreeSWITCH version, configuration, and command. Parsing should be result-based and configurable:

```php
enum ParseMode
{
    case Lenient;
    case Strict;
}
```

Result examples:

```php
ConferenceCommandParseResult::ok($reply);
ConferenceCommandParseResult::partial($reply, $blockers);
ConferenceCommandParseResult::unknownFormat($raw, $blockers);
ConferenceCommandParseResult::failed($reason, $blockers);
```

Rules:

```text
- lenient mode returns partial snapshots plus blockers when possible;
- strict mode fails closed when required fields are missing or reply format is unsupported;
- ordinary unknown FreeSWITCH text should not throw unless strict mode explicitly requests exception behavior;
- raw reply text may be retained in redacted/debug-safe form when useful;
- unsupported formats must produce stable blocker vocabulary.
```

---

## 6.3 Domain Models

Namespace:

```php
Apntalk\EslConference\Model
```

Stable `0.1.0` value objects:

```text
ConferenceName
ConferenceProfile
ConferenceMemberId
ConferenceChannelUuid
ConferenceAction
ConferenceEventSubclass
ConferenceObservationTimestamp
```

Stable `0.1.0` models:

```text
ConferenceMemberIdentity
ConferenceMemberState
ConferenceRoomSnapshot
ConferenceMemberSnapshot
ConferenceChannelIdentity
ConferenceCallerIdentity
```

Do not over-model conference names too early.

`ConferenceName` should preserve the original value and expose only conservative helpers:

```php
final readonly class ConferenceName
{
    public function raw(): string;

    public function hasProfileCandidate(): bool;

    public function profileNameCandidate(): ?string;

    public function roomNameCandidate(): ?string;
}
```

Rules:

```text
- accept common FreeSWITCH conference name shapes;
- preserve the original string;
- parse only when safe;
- treat parsed room/profile/domain values as candidates, not guaranteed semantics;
- do not assume APNTalk naming conventions;
- do not make ConferenceDomain stable in 0.1.0 unless real fixtures require it;
- support unknown/missing optional fields;
- keep all objects immutable;
- provide deterministic toArray() serialization;
- avoid secrets in serialized summaries.
```

Common accepted raw examples:

```text
room
room@profile
room-domain@profile
3000
3000@default
support-1001@default
```

---

## 6.4 Events

Namespace:

```php
Apntalk\EslConference\Event
```

The key event source is FreeSWITCH `conference::maintenance`, exposed as a CUSTOM event subclass.

Stable `0.1.0` event wrappers:

```text
ConferenceMaintenanceEvent
ConferenceMemberJoinedEvent
ConferenceMemberLeftEvent
ConferenceUnknownMaintenanceAction
```

Deferred event wrappers:

```text
ConferenceMemberMutedEvent
ConferenceMemberUnmutedEvent
ConferenceMemberDeafEvent
ConferenceMemberUndeafEvent
ConferenceMemberTalkingEvent
ConferenceMemberStoppedTalkingEvent
ConferenceFloorChangedEvent
ConferenceDestroyedEvent
ConferenceCreatedEvent
ConferenceEnergyLevelChangedEvent
```

Supported stable `0.1.0` actions:

```text
add-member
del-member
```

Recognized but not necessarily stable until fixture-proven:

```text
start-talking
stop-talking
mute-member
unmute-member
deaf-member
undeaf-member
floor-change
conference-create
conference-destroy
kick-member
bgdial-result
```

Unknown actions must produce `ConferenceUnknownMaintenanceAction`, not a hard failure.

Event parsing should be result-based:

```php
ConferenceEventParseResult::recognized($event);
ConferenceEventParseResult::unknownAction($event, $blockers);
ConferenceEventParseResult::notConferenceMaintenance($rawEvent, $blockers);
ConferenceEventParseResult::incomplete($rawEvent, $blockers);
```

Expected extraction fields:

```text
Event-Subclass
Action
Conference-Name
Conference-Profile-Name
Conference-Size
Member-ID
Member-Type
Channel-Name
Unique-ID
Caller-Caller-ID-Number
Caller-Caller-ID-Name
Caller-Destination-Number
Caller-Username
Caller-Context
```

Rules:

```text
- Event-Subclass and Action are required to classify a conference maintenance event;
- Conference-Name is required for a complete conference observation;
- Member-ID and Unique-ID may be required for complete member observations, depending on action;
- missing optional headers must be tolerated;
- missing critical fields must produce blocker-bearing parse results;
- retain the normalized esl-core substrate or a redacted summary for downstream consumers that need raw detail;
- do not use APNTalk terminology in event classes.
```

---

## 6.5 Observations

Namespace:

```php
Apntalk\EslConference\Observation
```

Events and replies are FreeSWITCH protocol facts. Observations are downstream-friendly, provider-neutral facts derived from events, replies, snapshots, or replayed evidence.

Stable `0.1.0` observations:

```text
ConferenceObservation
MemberPresenceObservation
MemberJoinedObservation
MemberLeftObservation
ConferenceSnapshotObservation
ConferenceObservationFactory
```

Observation objects answer:

```text
What did FreeSWITCH say happened?
Which conference?
Which member, if known?
Which channel, if known?
When was it observed?
What was the source: event, command reply, or replay?
How complete/confident is the evidence?
Which blockers were present?
```

Observation objects do not answer:

```text
Which tenant?
Which APNTalk binding?
Is lifecycle conferenceReady?
Is lifecycle callReady?
Should APNTalk route or advance a session?
```

Observation source/confidence enums:

```php
enum ObservationSource
{
    case Event;
    case CommandReply;
    case Replay;
}

enum ObservationConfidence
{
    case Observed;
    case Partial;
    case Unknown;
}
```

Observation payload must include:

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
```

Rules:

```text
- no secrets;
- no APNTalk IDs;
- immutable objects;
- deterministic toArray();
- explicit blockers for partial evidence;
- source metadata must be generic and connection-scoped.
```

---

## 6.6 Vocabulary

Namespace:

```php
Apntalk\EslConference\Vocabulary
```

Blocker/error terms:

```php
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
```

Vocabulary rules:

```text
- terms must remain FreeSWITCH/mod_conference-specific;
- terms must not become APNTalk lifecycle blockers;
- every blocker must be documented in docs/event-vocabulary.md or docs/command-vocabulary.md;
- blockers must be stable enough for downstream consumers to branch on.
```

---

## 7. Multi-PBX Readiness by Design

Even though `esl-conference` should not know APNTalk’s provider-node model, its objects must support multi-PBX consumers.

Do this by making package objects:

```text
immutable
serializable
source-context attachable
free of global state
free of singleton FreeSWITCH assumptions
```

Package-level optional metadata should be generic:

```php
final readonly class RuntimeObservationContext
{
    public function __construct(
        public ?string $sourceConnectionId,
        public ?string $sourceCoreUuid,
        public ?DateTimeImmutable $receivedAt,
        public ?int $eventSequence,
        public ?string $correlationId,
    ) {}
}
```

Do not add:

```text
tenantId
providerBindingId
sipAccountId
campaignId
leadId
APNTalk session id
```

APNTalk or another downstream system can attach its own context outside the package.

---

## 8. Replay Support

Replay support is useful, but it is not required for the first APNTalk unlock release.

Move replay envelope support to `0.3.0` unless needed earlier.

When introduced, `esl-conference` may emit replay-friendly observation envelopes but must not own storage.

`esl-replay` owns durable replay/evidence over stored artifacts, not live runtime operation.

Recommended later package behavior:

```text
ConferenceObservationEnvelope
  artifactName = "freeswitch.conference.observation"
  artifactVersion = "0.1"
  capturedAt
  payload
  sourceEventFingerprint
  replayDeterminismMetadata
```

Rules:

```text
- do not write to filesystem;
- do not write to DB;
- do not require esl-replay;
- provide optional interface only if needed;
- preserve deterministic fingerprint behavior;
- same observation input should produce same fingerprint output.
```

Optional later interface:

```php
interface ConferenceReplayEnvelopeFactoryInterface
{
    public function fromObservation(ConferenceObservation $observation): ConferenceObservationEnvelope;
}
```

APNTalk or `esl-react` can pass generated envelopes to `esl-replay`.

---

## 9. FreeSWITCH Vocabulary Provenance

Create:

```text
docs/freeswitch-vocabulary-provenance.md
```

This document is mandatory before declaring command/event/reply support stable.

It should record:

```text
FreeSWITCH version tested
mod_conference command tested
exact command string used
raw reply fixture path
parsed model produced
event fixture path
fields observed
fields absent
unsupported/version-specific fields
live validation date
operator or environment notes
sanitization notes
```

Example table:

```text
| Vocabulary item | Type | Stable since | Evidence | Fixture | Notes |
| --- | --- | --- | --- | --- | --- |
| conference::maintenance add-member | event action | 0.1.0 | live lab FS x.y.z | tests/Fixture/events/add-member.event | Member-ID observed |
| conference <name> list | command/reply | 0.1.0 | live lab FS x.y.z | tests/Fixture/replies/conference_list_members.txt | Parser lenient |
| conference <name> xml_list | command/reply | provisional | pending | n/a | Do not expose stable API yet |
| conference <name> json_list | command/reply | unsupported/provisional | pending | n/a | Must not be required by APNTalk |
```

Release-facing docs must not claim support for an event action, command, or reply shape unless this provenance document contains evidence.

---

## 10. Implementation Phases

## Phase 0 — Repository Creation and Baseline

Create repository:

```text
apntalk/esl-conference
```

Composer metadata:

```json
{
  "name": "apntalk/esl-conference",
  "type": "library",
  "description": "Framework-agnostic FreeSWITCH mod_conference domain primitives for PHP ESL integrations.",
  "require": {
    "php": "^8.3",
    "apntalk/esl-core": "^0.2"
  },
  "autoload": {
    "psr-4": {
      "Apntalk\\EslConference\\": "src/"
    }
  }
}
```

Baseline tools:

```text
PHPUnit
PHPStan
Psalm optional
PHP-CS-Fixer
GitHub Actions
composer unit
composer analyse
composer cs-check
composer check
```

Docs:

```text
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
composer check passes
public API documented
package boundaries documented
FreeSWITCH vocabulary provenance document created
no framework dependencies
no runtime connection ownership
```

---

## Phase 1 — Conference Domain Model

Implement immutable value objects:

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
```

Implement models:

```text
ConferenceMemberIdentity
ConferenceMemberState
ConferenceRoomSnapshot
ConferenceMemberSnapshot
ConferenceChannelIdentity
ConferenceCallerIdentity
```

Rules:

```text
preserve original FreeSWITCH values
parse only when safe
support unknown/missing optional fields
fail closed for invalid outbound command values
no APNTalk IDs
no DB assumptions
no runtime dependencies
deterministic toArray() serialization
```

Tests:

```text
ConferenceNameTest
ConferenceMemberIdTest
ConferenceChannelUuidTest
ConferenceActionTest
ConferenceCommandTokenTest
ConferenceMemberSelectorTest
ConferenceRoomSnapshotTest
```

Exit criteria:

```text
domain model handles common and unknown values
invalid outbound values fail clearly
observed inbound values can preserve raw FreeSWITCH strings safely
all objects serialize to arrays deterministically
```

---

## Phase 2 — `conference::maintenance` Event Normalization

Implement:

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

Input:

```text
apntalk/esl-core normalized event / custom event wrapper
```

Output:

```text
ConferenceEventParseResult
```

Stable `0.1.0` actions:

```text
add-member
del-member
```

Unknown actions produce:

```text
ConferenceUnknownMaintenanceAction
```

not a hard failure.

Tests:

```text
ConferenceMaintenanceEventFactoryTest
ConferenceMemberJoinedEventTest
ConferenceMemberLeftEventTest
ConferenceUnknownActionTest
ConferenceIncompleteEventTest
ConferenceNotMaintenanceEventTest
```

Fixtures:

```text
tests/Fixture/events/conference_member_joined.event
tests/Fixture/events/conference_member_left.event
tests/Fixture/events/conference_unknown_action.event
tests/Fixture/events/conference_missing_name.event
tests/Fixture/events/non_conference_custom_event.event
```

Exit criteria:

```text
member join/leave parsed from fixtures
unknown actions retained safely
missing optional headers tolerated
missing critical classification fields produce blocker result
event actions documented in docs/freeswitch-vocabulary-provenance.md
```

---

## Phase 3 — Conference Command Builders

Implement stable command object:

```text
ConferenceListMembersCommand
```

Optional if fixture-proven:

```text
ConferenceXmlListCommand
```

Do not include as stable in `0.1.0`:

```text
ConferenceJsonListCommand
ConferenceBgdialCommand
ConferenceKickMemberCommand
```

Example:

```php
ConferenceListMembersCommand::forConference($name)->toCommandText();
// conference <name> list
```

Rules:

```text
validate unsafe outbound arguments
reject CR/LF and NUL bytes
reject empty conference names
do not execute commands
do not know connection runtime
do not know APNTalk
record supported command vocabulary in provenance docs
```

Tests:

```text
ConferenceListMembersCommandTest
ConferenceCommandTokenTest
ConferenceCommandSafetyTest
```

Exit criteria:

```text
command text is deterministic
invalid conference names fail safely
unsafe command arguments fail closed
no runtime dependency
stable commands are backed by provenance docs
```

---

## Phase 4 — Reply Parsing and Snapshots

Implement parsers:

```text
ConferenceListReplyParser
ConferenceMemberListParser
```

Optional if fixture-proven:

```text
ConferenceXmlListReplyParser
```

Do not include as stable in `0.1.0`:

```text
ConferenceJsonListReplyParser
```

Outputs:

```text
ConferenceRoomSnapshot
ConferenceMemberSnapshot[]
ConferenceCommandParseResult
```

Parsing modes:

```text
ParseMode::Lenient
ParseMode::Strict
```

Lenient mode should return partial snapshot plus blockers when possible.

Tests:

```text
ConferenceListReplyParserTest
ConferenceMemberListParserTest
ConferenceMemberSnapshotTest
ConferenceReplyStrictModeTest
ConferenceReplyLenientModeTest
```

Fixtures:

```text
tests/Fixture/replies/conference_list_empty.txt
tests/Fixture/replies/conference_list_members.txt
tests/Fixture/replies/conference_unparseable.txt
tests/Fixture/replies/conference_rejected.txt
```

Optional/proven fixtures:

```text
tests/Fixture/replies/conference_xml_list.xml
```

Deferred/provisional fixtures:

```text
tests/Fixture/replies/conference_json_list.json
```

Exit criteria:

```text
empty conference parsed
member list parsed
unparseable reply fails closed with blocker
partial replies produce partial result in lenient mode
strict mode rejects unsupported/missing critical fields
snapshot model stable
reply format evidence recorded in docs/freeswitch-vocabulary-provenance.md
```

---

## Phase 5 — Observation Model

Implement:

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

Observation payload must include:

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
```

No secrets.

No APNTalk IDs.

Tests:

```text
ConferenceObservationFactoryTest
MemberPresenceObservationTest
ConferenceSnapshotObservationTest
PartialObservationBlockerTest
ObservationSerializationTest
```

Exit criteria:

```text
events convert to observations
snapshots convert to observations
partial evidence has blockers
observations serialize deterministically
no APNTalk terms in observation source/API except boundary docs
```

---

## Phase 6 — Boundary and Negative Vocabulary Tests

Add a boundary test suite that prevents APNTalk or framework leakage.

Test source directories and public docs for forbidden terms:

```text
tenant
provider_binding
sip_account
campaign
lead
callReady
conferenceReady
Laravel
Eloquent
database
worker
supervisor
```

Allowed exceptions:

```text
docs/package-boundaries.md
docs/downstream-apntalk-integration.md
```

Those docs may mention forbidden terms only to state that they are owned outside `esl-conference`.

Tests:

```text
NoApntalkVocabularyInSourceTest
NoFrameworkDependencyTest
NoRuntimeOwnershipVocabularyTest
ComposerDependencyBoundaryTest
```

Exit criteria:

```text
source code has no APNTalk lifecycle vocabulary
composer.json has no framework/runtime dependency creep
docs clearly separate package facts from APNTalk lifecycle decisions
```

---

## Phase 7 — Optional `esl-react` Integration Helpers

Defer this phase until after `0.1.0` unless immediately needed.

Do not make `esl-react` a hard dependency.

Use Composer `suggest` or a separate optional adapter package if dependency boundaries become awkward.

Optional namespace:

```php
Apntalk\EslConference\Integration\React
```

Possible helpers:

```text
ConferenceRuntimeClient
ConferenceEventSubscription
ConferenceCommandDispatcher
```

These should accept small package-local interfaces:

```php
interface EslCommandDispatcher
{
    public function api(string $command): mixed;

    public function bgapi(string $command): mixed;
}

interface EslEventStream
{
    public function subscribe(string $eventName, callable $listener): mixed;
}
```

Avoid hard-coding a Promise implementation in the base package unless this is isolated behind optional integration code.

Exit criteria:

```text
adapter helpers compile without forcing esl-react install
fake dispatcher/event stream tests prove behavior
base package remains useful without runtime adapter
```

---

## Phase 8 — Replay Envelope Support

Defer this phase until `0.3.0` unless needed earlier.

Implement optional replay envelope factory:

```text
ConferenceObservationEnvelope
ConferenceObservationEnvelopeFactory
ConferenceObservationFingerprint
```

No storage.

No replay execution.

No dependency on `esl-replay` unless only an interface is required and dependency policy explicitly allows it.

Exit criteria:

```text
conference observation can be exported into deterministic replay-safe payload
same observation produces stable fingerprint
no filesystem or DB writes
no live runtime behavior
```

---

## Phase 9 — APNTalk Integration Contract Document

Create:

```text
docs/downstream-apntalk-integration.md
```

This doc should specify that APNTalk owns mapping:

```text
FreeSWITCH conference observation
→ provider_node_key
→ provider_binding_id
→ conference_session_id
→ canonical conference observation
→ conferenceReady/callReady
```

It should explicitly say:

```text
esl-conference does not decide conferenceReady or callReady.
```

Illustrative APNTalk adapter shape:

```php
final class FreeSwitchConferenceProviderAdapter
{
    public function ingestConferenceObservation(
        ProviderRuntimeContext $context,
        ConferenceObservation $observation
    ): CanonicalConferenceObservationResult {
        // APNTalk-owned mapping.
    }
}
```

This class must remain documentation-only. It must not become part of `esl-conference` source.

Exit criteria:

```text
APNTalk integration boundary is documented
no APNTalk dependency is added
lifecycle readiness language is explicitly downstream-owned
```

---

## 11. Versioning Roadmap

## `0.1.0`

Goal: stable, fixture-proven basic conference event, command, reply, snapshot, and observation primitives.

Includes:

```text
ConferenceName
ConferenceMemberId
ConferenceChannelUuid
ConferenceMaintenanceEvent
ConferenceEventParseResult
member join/leave parsing
unknown maintenance action handling
ConferenceListMembersCommand
conference list reply parser
ConferenceRoomSnapshot
ConferenceMemberSnapshot
MemberJoinedObservation
MemberLeftObservation
ConferenceSnapshotObservation
blocker vocabulary
boundary tests
FreeSWITCH vocabulary provenance docs
APNTalk downstream integration boundary docs
```

Does not include as stable:

```text
json_list parser
bgdial command
kick command
mute/deaf command family
recording command family
runtime adapter helpers
replay envelope factory
```

## `0.2.0`

Goal: command/reply coverage expansion after provenance exists.

Potential includes:

```text
bgdial
kick
mute/unmute
deaf/undeaf
xml_list parser if not already included
JSON list parser only if live-proven
snapshot diff helper
additional conference::maintenance actions
```

## `0.3.0`

Goal: runtime/replay integration helpers.

Potential includes:

```text
optional esl-react adapter interfaces
replay envelope factory
conference observation fingerprint
operator-run live validation helper scripts
```

## `1.0.0`

Goal: stable public API.

Required before `1.0.0`:

```text
public API documented
BC policy documented
fixtures for every supported event/reply format
FreeSWITCH vocabulary provenance complete for stable claims
APNTalk downstream integration proven
FreeSWITCH live lab validation completed
PHPStan/Psalm baseline clean or justified
negative-boundary tests passing
```

---

## 12. Testing Strategy

Package tests must not require live FreeSWITCH by default.

Default test layers:

```text
unit:
  value objects, parser logic, command builders

contract:
  public API shape, serialization, blocker vocabulary, BC-sensitive behavior

fixture:
  real/sanitized FreeSWITCH event and reply fixtures

integration:
  fake ESL dispatcher/event stream only, no live sockets

boundary:
  no APNTalk vocabulary, no framework dependency creep, no runtime ownership

optional live:
  operator-run FreeSWITCH validation, excluded from default CI
```

Composer scripts:

```json
{
  "scripts": {
    "unit": "phpunit --testsuite Unit",
    "contract": "phpunit --testsuite Contract",
    "fixture": "phpunit --testsuite Fixture",
    "integration": "phpunit --testsuite Integration",
    "boundary": "phpunit --testsuite Boundary",
    "analyse": "phpstan analyse",
    "cs-check": "php-cs-fixer fix --dry-run --diff",
    "check": [
      "@cs-check",
      "@analyse",
      "@unit",
      "@contract",
      "@fixture",
      "@integration",
      "@boundary"
    ],
    "live-check": "phpunit --testsuite Live"
  }
}
```

Optional live validation:

```text
composer live-check
```

Keep live validation out of default CI.

---

## 13. Live Validation Plan

After event/reply parsing exists, validate with a FreeSWITCH lab.

Live validation should prove:

```text
subscribe to conference::maintenance
join a conference
observe add-member event
parse member joined event into typed event
convert typed event into MemberJoinedObservation
run conference <name> list
parse reply into ConferenceRoomSnapshot
observe del-member event
parse member left event into typed event
convert typed event into MemberLeftObservation
record sanitized fixtures
update docs/freeswitch-vocabulary-provenance.md
```

Replay envelope export is not required for `0.1.0`. Add it to live validation only after replay support lands.

This validation belongs in package docs/tools, not APNTalk lifecycle closure.

APNTalk separately proves:

```text
FreeSWITCH observation → canonical lifecycle conferenceReady/callReady
```

---

## 14. What APNTalk Should Wait For

APNTalk FreeSWITCH conference parity should wait until `esl-conference` has at least:

```text
ConferenceMaintenanceEvent
ConferenceEventParseResult
MemberJoinedObservation
MemberLeftObservation
ConferenceListMembersCommand
ConferenceMemberListParser or equivalent proven list parser
ConferenceRoomSnapshot
ConferenceMemberSnapshot
ConferenceObservation
blocker vocabulary
boundary docs
fixture provenance
```

Then APNTalk can implement:

```text
FreeSwitchConferenceProviderAdapter
FreeSwitchCallReadyProviderAdapter
```

without parsing FreeSWITCH protocol details inside APNTalk.

APNTalk should not wait for `json_list` unless APNTalk explicitly chooses to depend on a fixture-proven JSON reply format later.

---

## 15. What APNTalk Should Not Wait For

APNTalk does not need to wait for:

```text
full conference admin command coverage
recording support
mute/deaf/full moderator control
replay execution
replay envelope support
runtime adapter helpers
daemon/supervisor logic
multi-PBX policy package
JSON list support unless proven and explicitly selected
```

Those can come later.

---

## 16. Acceptance Criteria

The `esl-conference` package is ready for APNTalk integration when:

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

APNTalk integration can begin after that.

---

## 17. Practical Bottom Line

Create `apntalk/esl-conference` now.

Keep it small in responsibility but not merely “minimal to pass.” Its purpose is to become the reusable `mod_conference` domain layer for FreeSWITCH ESL.

The first release should be deliberately narrow:

```text
0.1.0 = conference::maintenance join/leave + conference list snapshot parsing + observations + blockers + provenance + boundary docs.
```

Defer unstable or unproven pieces:

```text
json_list
expanded admin commands
runtime helpers
replay envelopes
```

The package should answer:

```text
What did FreeSWITCH mod_conference command/event/reply say?
```

APNTalk should answer:

```text
Does that FreeSWITCH fact make this tenant/account/binding/session conferenceReady or callReady?
```

That boundary keeps the package generic, multi-PBX-friendly, and useful beyond APNTalk while preventing FreeSWITCH provider logic from leaking into canonical lifecycle authority.
