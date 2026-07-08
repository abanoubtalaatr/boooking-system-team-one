<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class FaqResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'question',
        'answer',
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'category',
    ];
}
