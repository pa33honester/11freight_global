<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case RECEIVED = 'RECEIVED';
    case IN_WAREHOUSE = 'IN_WAREHOUSE';
    case IN_CONTAINER = 'IN_CONTAINER';
    case DISPATCHED = 'DISPATCHED';
    case ARRIVED_GHANA = 'ARRIVED_GHANA';
    case DELIVERED = 'DELIVERED';
    case VOID = 'VOID';

    public static function values(): array
    {
        return array_map(fn(ShipmentStatus $s) => $s->value, self::cases());
    }
}
