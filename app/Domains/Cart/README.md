# Cart

The pre-purchase basket, including guest carts and merging on login.

## Owns

Cart, CartItem; add/update/remove, validation and the guest-to-user merge.

## Does not own

Placing the order (Ordering).

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.6, FR-CART
