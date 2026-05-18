# Reply Formats

## Stable Reply Shape

The stable 0.1.0 parser supports text replies for `conference <name> list`.

The live lab observed room-specific list replies as member rows without a room header:

```text
1;loopback/9196-a;11111111-1111-4111-8111-111111111111;Outbound Call;9196;hear|speak|floor;0;0;100
```

For this shape, callers must provide the conference name to `ConferenceListReplyParser::parse()` because the reply text does not carry it.

The parser also accepts fixture-backed header shapes for `conference list` or older representative contract fixtures:

```text
+OK Conference support-1001 (1 members rate: 8000 flags: running|answered)
1;loopback/9196-a;11111111-1111-4111-8111-111111111111;Outbound Call;9196;hear|speak|floor;0;0;100
```

```text
Conference support-1001@default (0 members)
```

The member parser accepts semicolon-delimited lines with at least these fields:

```text
member id;channel name;channel uuid;caller name;caller number;flags
```

Additional trailing fields are preserved only as part of the supported row tolerance; they are not interpreted as stable 0.1.0 fields.

Lenient mode can return partial snapshots with blockers. Strict mode fails closed when required fields are missing or the reply is unsupported.

## Blockers

- `conference_reply_unparseable`
- `conference_reply_unsupported`
- `conference_member_id_missing`
- `conference_channel_uuid_missing`
- `conference_command_rejected`
- `conference_snapshot_unsupported`
