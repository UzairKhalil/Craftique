# Identity

Users, authentication, roles, sessions, 2FA, addresses and preferences.

## Owns

User, Address, SocialAccount, UserPreference, UserDevice; registration, login, verification, password reset, 2FA, account deletion.

## Does not own

Anything vendor-scoped (that is Vendor), or customer purchase history (that is Ordering).

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.1, FR-AUTH
