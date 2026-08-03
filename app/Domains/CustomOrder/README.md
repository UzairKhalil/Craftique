# CustomOrder

Bespoke commissions: briefs, clarification, quotations and conversion to a real order.

## Owns

CustomRequest, Quotation, QuotationItem, ProductionMilestone.

## Does not own

Fulfilment after conversion, which uses the ordinary Ordering pipeline.

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.10, FR-CUSTOM
