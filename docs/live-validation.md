# Live Validation

Default checks do not require a live FreeSWITCH instance.

Live validation is operator-run only:

```bash
composer live-check
```

The live suite should be enabled with explicit local environment variables and should capture sanitized fixtures only when an operator chooses to refresh evidence.

Suggested local variables:

```bash
ESL_CONFERENCE_LIVE_TEST=1
ESL_CONFERENCE_LIVE_HOST=::1
ESL_CONFERENCE_LIVE_PORT=8021
ESL_CONFERENCE_LIVE_PASSWORD=change-me-local-only
ESL_CONFERENCE_LIVE_CONFERENCE=support-1001
```

Fixture refresh is opt-in:

```bash
ESL_CONFERENCE_CAPTURE_FIXTURES=1
```

The local Docker lab used for 0.1.0 evidence is under `docker/`. A bounded validation run starts the lab, connects over ESL, verifies `mod_conference`, creates a controlled loopback conference member, observes `add-member`, runs `conference <name> list`, observes a rejected missing-conference list reply, hangs up the conference, and observes `del-member`.

The captured 0.1.0 evidence command was:

```bash
TMPDIR=.tmp ESL_CONFERENCE_LIVE_TEST=1 ESL_CONFERENCE_CAPTURE_FIXTURES=1 ESL_CONFERENCE_LIVE_HOST=::1 ESL_CONFERENCE_LIVE_PORT=8021 ESL_CONFERENCE_LIVE_PASSWORD=<local-dev-password> ESL_CONFERENCE_LIVE_CONFERENCE=live-015 composer live-check
```

The live test setup uses FreeSWITCH conference control commands internally to create and clear the controlled member. Those setup commands are not stable public command builders in this package.

Do not commit real secrets. Do not include live validation in `composer check`.
