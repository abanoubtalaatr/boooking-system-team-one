<?php

namespace App\Enums;

enum SlotReservationStatus: string
{
    case Available = 'available';
    case Held = 'held';
    case Booked = 'booked';
}
