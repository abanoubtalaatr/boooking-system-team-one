<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = "active";
    case PendingProfile = "pending_profile";
    case Suspended = "suspended";
}
