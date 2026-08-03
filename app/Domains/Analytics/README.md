# Analytics

Event capture and the rollups that dashboards and reports read.

## Owns

AnalyticsEvent, VendorDailyStats, ProductDailyStats, PlatformDailyStats; reporting queries.

## Does not own

Presentation. Dashboards read rollups, never raw tables.

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.16
