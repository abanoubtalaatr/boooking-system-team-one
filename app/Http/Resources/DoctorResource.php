<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class DoctorResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'name',
        'email',
        'created_at',
        'experience_years',
        'consultation_fee',
        'rating',
        'address',
        'latitude',
        'longitude',
        'image',
        'is_available',
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        // ...
        'user',
        'specialization',
    ];
}
