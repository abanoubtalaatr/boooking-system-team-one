<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class PromotionResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'title',
        'description',
        'image',
        'start_date',
        'end_date',
        'is_active',
    ];

}
