# Platform

Running the business: settings, feature flags, audit log, moderation queues, health.

## Owns

Setting, FeatureFlag, ActivityLog, ModerationQueue, ImpersonationLog.

## Does not own

Domain-specific admin screens, which belong to their domain.

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.15, FR-ADMIN
