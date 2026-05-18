# Architecture

`esl-conference` is a pure protocol/domain package. Callers provide normalized FreeSWITCH event headers and command reply text. The package returns value objects, parse results, snapshots, observations, and blocker codes.

The package has no socket ownership, no process control, no persistence layer, and no framework bootstrapping. Transport-specific packages can execute commands and subscribe to events outside this package, then pass normalized facts into this package.

## Data Flow

```text
normalized event headers -> ConferenceMaintenanceEventFactory -> event parse result -> observation
command reply text -> ConferenceListReplyParser -> command parse result -> snapshot observation
ConferenceName -> ConferenceListMembersCommand -> command text
```

## Stability

The 0.1.0 stable surface is limited to fixture-covered `conference::maintenance` join and leave actions plus `conference <name> list` command/reply handling. The bounded add-member, del-member, member list, and rejected list reply path has sanitized live fixture evidence from FreeSWITCH 1.10.11 in the local lab. Unsupported FreeSWITCH features remain outside the stable API until evidence is added.
