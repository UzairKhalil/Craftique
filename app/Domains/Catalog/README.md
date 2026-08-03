# Catalog

Everything a vendor lists: categories, products, variants, options, attributes, tags, collections, media, personalisation.

## Owns

Product, ProductVariant, Category, Collection, Attribute, Tag, PersonalizationField; publishing and moderation.

## Does not own

Stock levels (Inventory), prices beyond the stored amount (Pricing).

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.3, FR-CAT
