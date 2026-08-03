# Review

Ratings and written feedback on products and vendors, and their moderation.

## Owns

Review, ReviewReply, ReviewVote, ContentReport; verified-purchase rules and aggregates.

## Does not own

The rating denormalised onto Product/Vendor is written here but owned there.

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.12, FR-REVIEW
