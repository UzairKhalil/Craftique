# Messaging

Conversations between customers, vendors and staff.

## Owns

Conversation, Message, MessageAttachment, MessageRead; real-time delivery, read state and presence.

## Does not own

Notifications about missed messages (Notification).

## Boundaries

Per ADR-0014 this module may depend on `Shared`, and may react to other domains
via **events**. It must not import another domain's internal services, actions or
queries. The architecture tests in `tests/Architecture` enforce this.

## Reference

PROJECT_PLAN §11.11, ADR-0007, FR-CHAT
