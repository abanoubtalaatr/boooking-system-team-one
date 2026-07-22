<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Actions\Specialization\Dashboard\CreateSpecializationAction;
use App\Actions\Specialization\Dashboard\GetSpecializationsAction;
use App\Actions\Specialization\Dashboard\UpdateSpecializationAction;
use App\Actions\Specialization\Dashboard\DeleteSpecializationAction;
use App\Models\Specialization;

class SpecializationService
{
    public function __construct(
        protected GetSpecializationsAction      $getSpecializationsAction,
        protected CreateSpecializationAction    $createSpecializationAction,
        protected UpdateSpecializationAction    $updateSpecializationAction,
        protected DeleteSpecializationAction    $deleteSpecializationAction,
    ) {}

    public function index(): array
    {
        return [
            'specializations' => ($this->getSpecializationsAction)(),
            'totalSpecializations' => Specialization::count(),
        ];
    }

    public function store(array $data): Specialization
    {
        return ($this->createSpecializationAction)($data);
    }

    public function edit(Specialization $specialization): Specialization {
        return $specialization;
    }

    public function update(Specialization $specialization, array $data): Specialization {

        return ($this->updateSpecializationAction)(
            $specialization,
            $data
        );
    }

    public function destroy(Specialization $specialization): void {
        ($this->deleteSpecializationAction)(
            $specialization
        );
    }
}
