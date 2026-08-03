# Shipping

Rates, zones, methods, shipments and carrier tracking.

## Owns

ShippingProfile, ShippingZone, ShippingMethod, Shipment, TrackingEvent; carrier drivers.

## Does not own

Order status transitions (Ordering).

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.9, FR-SHIP
