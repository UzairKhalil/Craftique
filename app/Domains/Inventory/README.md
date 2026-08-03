# Inventory

Stock levels, reservations, movements and low-stock signals.

## Owns

InventoryMovement, StockReservation, BackInStockSubscription; the atomic decrement and reservation lifecycle.

## Does not own

The variant itself (Catalog).

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.4
