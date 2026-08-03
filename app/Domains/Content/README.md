# Content

Editorial surfaces: CMS pages, banners, homepage merchandising, FAQs, SEO metadata.

## Owns

Page, Banner, HomepageSection, Faq, Redirect.

## Does not own

Product data (Catalog).

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.15, §28
