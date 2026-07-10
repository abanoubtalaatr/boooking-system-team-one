<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class PolicyResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'type',
        'content',
        'is_active',
    ];
}
