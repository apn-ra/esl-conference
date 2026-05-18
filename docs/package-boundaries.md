# Package Boundaries

This package owns generic FreeSWITCH `mod_conference` facts and transformations.

It does not own tenant, provider_binding, sip_account, campaign, lead, conferenceReady, callReady, Laravel, Eloquent, database, worker, supervisor, canonical lifecycle, or lifecycle snapshot concepts. Those names describe downstream application responsibilities and must not appear in `src/` or stable package APIs.

Allowed responsibilities:

- command text generation for proven `mod_conference` commands;
- event normalization for proven `conference::maintenance` actions;
- reply parsing for proven command replies;
- conference snapshots and observations;
- FreeSWITCH conference blocker vocabulary;
- fixture provenance.

Forbidden responsibilities:

- application readiness decisions;
- provider selection;
- persistence;
- socket ownership;
- reconnect behavior;
- process control;
- framework service providers.
