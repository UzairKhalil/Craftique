# Ordering

Orders, per-vendor sub-orders, fulfilment state, returns and disputes.

## Owns

Order, VendorOrder, OrderItem, OrderTimelineEvent, ReturnRequest, Dispute, Invoice; the vendor-order state machine.

## Does not own

Taking money (Payment) and moving parcels (Shipping).

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.7, ADR-0003, FR-ORDER
