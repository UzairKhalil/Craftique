# Pricing

What a line actually costs: coupons, flash sales, gift cards and tax.

## Owns

Coupon, FlashSale, GiftCard, TaxRate; DiscountEngine and TaxCalculator.

## Does not own

Order totals assembly (Ordering) and commission (Payment).

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.5, FR-CART-5/6
