<?php

declare(strict_types=1);

namespace App\Domains\Shared\Config;

use InvalidArgumentException;

/**
 * Messaging limits and abuse controls (FR-CHAT-10, FR-CHAT-11).
 *
 * @see config/craftique.php
 */
final readonly class ChatRules
{
    public function __construct(
        public int $messagesPerMinute,
        public int $newConversationsPerDay,
        public int $notifyAfterUnreadMinutes,
        public int $notificationThrottleMinutes,
        public string $contactSharingPolicy,
    ) {}

    /**
     * @param  array<string, mixed>  $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            messagesPerMinute: self::int($values, 'messages_per_minute'),
            newConversationsPerDay: self::int($values, 'new_conversations_per_day'),
            notifyAfterUnreadMinutes: self::int($values, 'notify_after_unread_minutes'),
            notificationThrottleMinutes: self::int($values, 'notification_throttle_minutes'),
            contactSharingPolicy: self::string($values, 'contact_sharing_policy'),
        );
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private static function int(array $values, string $key): int
    {
        $value = $values[$key] ?? null;

        if (! is_int($value)) {
            throw new InvalidArgumentException("craftique.{$key} must be an integer.");
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private static function string(array $values, string $key): string
    {
        $value = $values[$key] ?? null;

        if (! is_string($value) || $value === '') {
            throw new InvalidArgumentException("craftique.{$key} must be a non-empty string.");
        }

        return $value;
    }
}
