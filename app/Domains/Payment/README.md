# Payment

Money in, money out, and the ledger that proves both.

## Owns

Payment, Refund, Commission, LedgerEntry, Payout; gateway drivers and webhook handling.

## Does not own

Order state (Ordering). Money is always integer minor units (ADR-0004).

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.8, ADR-0004/0006, FR-PAY
