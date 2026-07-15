<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class SpecializationResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'id',
        'name',
        'image',
    ];
}
