<?php

namespace App\Services;

use App\Actions\Specialization\GetSpecializationsAction;

class SpecializationService
{
    public function __construct(protected GetSpecializationsAction $getSpecializationsAction) {}

    public function index()
    {
        return ($this->getSpecializationsAction)();
    }
}
