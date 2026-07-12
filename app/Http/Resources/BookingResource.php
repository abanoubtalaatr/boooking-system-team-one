<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class BookingResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'booking_number',
        'booking_date',
        'booking_time',
        'consultation_type',
        'price',
        'status',
        'payment_status',
        'created_at',
        'updated_at',
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'patient',
        'doctor',
        'slot',
    ];
}
