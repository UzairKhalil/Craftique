# Vendor

Stores as organisations: onboarding, verification, staff, themes, followers, subscriptions.

## Owns

Vendor, VendorUser, VendorVerification, VendorTheme, VendorFollower, VendorSubscription; the approval workflow and vendor scoping.

## Does not own

Products (Catalog), earnings and payouts (Payment).

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.2, FR-VENDOR
