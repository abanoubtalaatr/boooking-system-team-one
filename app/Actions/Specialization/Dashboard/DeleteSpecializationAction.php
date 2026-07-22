<?php

declare(strict_types=1);

namespace App\Actions\Specialization\Dashboard;

use App\Models\Specialization;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteSpecializationAction
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Specialization $specialization): void {

        if ($specialization->doctors()->exists()) {
            throw ValidationException::withMessages([
                'specialization' => 'This specialization cannot be deleted because doctors are assigned to it.',
            ]);
        }

        if ($specialization->image && Storage::disk('public')->exists($specialization->image)) {
            Storage::disk('public')->delete(
                $specialization->image
            );
        }

        $specialization->delete();

        Cache::forget('specializations');
    }
}
