# Shared

The kernel every domain may depend on. The only domain others may import from freely.

## Owns

Money and other value objects, contracts/ports, base classes, traits, enums, exceptions.

## Does not own

Any business rule belonging to a specific domain. Keep this small.

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §9.3, ADR-0014
