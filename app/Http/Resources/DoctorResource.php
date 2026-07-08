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
        'created_at'
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        // ...
    ];
}
