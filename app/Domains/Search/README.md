# Search

Indexing and querying the catalogue.

## Owns

Index transformers, ProductSearchQuery, facets, synonyms, search analytics.

## Does not own

The source records themselves (Catalog, Vendor).

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.13, ADR-0008, FR-SEARCH
