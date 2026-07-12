<?php
namespace App\Enums;
enum BookingStatus:string
{
    case Pending='pending';
    case Confirmed='confirmed';
    case Completed='completed';
    case Cancelled='cancelled';
    case Rescheduled='rescheduled';
    case Rejected='rejected';
    case Expired='expired';
}
