<?php

declare(strict_types=1);

namespace App\Actions\Specialization\Dashboard;

use App\Models\Specialization;

class CreateSpecializationAction
{
    public function __invoke(array $data): Specialization
    {
        if (isset($data['image'])) {
            $data['image'] = $data['image']->store(
                'specializations',
                'public'
            );
        }

        return Specialization::create($data);
    }
}
