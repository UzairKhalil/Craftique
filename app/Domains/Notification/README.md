# Notification

Deciding who hears about what, on which channel.

## Owns

Notification classes, channel routing, per-user preferences, templates, digests and throttling.

## Does not own

The events themselves, which are raised by the owning domain.

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.14, FR-NOTIF
