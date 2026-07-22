<?php

declare(strict_types=1);

namespace App\Actions\Specialization\Dashboard;

use App\Models\Specialization;
use Illuminate\Support\Facades\Storage;

class UpdateSpecializationAction
{
    public function __invoke(Specialization $specialization, array $data): Specialization {

        if (isset($data['image'])) {
            if (
                $specialization->image &&
                Storage::disk('public')->exists($specialization->image)
            ) {
                Storage::disk('public')
                    ->delete($specialization->image);
            }

            $data['image'] = $data['image']
                ->store('specializations', 'public');
        }

        $specialization->update($data);

        return $specialization->refresh();
    }
}
