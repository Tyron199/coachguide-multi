<?php

namespace App\Enums\Tenant;

enum ContractStatus: int
{
    case DRAFT = 0;
    case SENT = 1;
    case VIEWED = 2;
    case SIGNED_CLIENT = 3;
    case COUNTERSIGNED = 4;
    case ACTIVE = 5;
    case LAPSED = 6;
    case TERMINATED = 7;
    case VOID = 8;

    /**
     * Get the label for the status
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::VIEWED => 'Viewed',
            self::SIGNED_CLIENT => 'Signed by Client',
            self::COUNTERSIGNED => 'Countersigned',
            self::ACTIVE => 'Active',
            self::LAPSED => 'Lapsed',
            self::TERMINATED => 'Terminated',
            self::VOID => 'Void',
        };
    }

    /**
     * Get the color class for UI display
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'blue',
            self::VIEWED => 'yellow',
            self::SIGNED_CLIENT => 'orange',
            self::COUNTERSIGNED => 'purple',
            self::ACTIVE => 'green',
            self::LAPSED => 'red',
            self::TERMINATED => 'red',
            self::VOID => 'red',
        };
    }

    /**
     * Get statuses that indicate contract is pending signature
     */
    public static function pendingSignature(): array
    {
        return [self::SENT, self::VIEWED];
    }

    /**
     * Get statuses that indicate contract is signed
     */
    public static function signed(): array
    {
        return [self::SIGNED_CLIENT, self::COUNTERSIGNED, self::ACTIVE];
    }

    /**
     * Get statuses that indicate contract is active/valid
     */
    public static function active(): array
    {
        return [self::ACTIVE];
    }

    /**
     * Get statuses that indicate contract is ended
     */
    public static function ended(): array
    {
        return [self::LAPSED, self::TERMINATED, self::VOID];
    }
} 