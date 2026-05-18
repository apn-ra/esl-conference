# Downstream Integration

Downstream APNTalk code owns tenant, provider_binding, sip_account, campaign, lead, conferenceReady, callReady, Laravel, Eloquent, database, worker, supervisor, canonical lifecycle, and lifecycle snapshot concerns.

`esl-conference` emits generic FreeSWITCH conference facts only. A downstream integration may attach its own application context outside this package and decide how those facts affect its own workflows.

No downstream identifier should be embedded in `ConferenceObservation`, snapshots, event wrappers, commands, or blocker values.
