<?php

namespace App\Services\Web;

use App\Models\Hospital;

class HospitalService
{
    public function create(array $data): Hospital
    {
        return Hospital::create($data);
    }

    public function update(Hospital $hospital, array $data): Hospital
    {
        $hospital->update($data);

        return $hospital;
    }

    /**
     * Delete the hospital. Returns false when it still has doctor profiles attached,
     * so the controller can show the appropriate message.
     */
    public function delete(Hospital $hospital): bool
    {
        if ($hospital->doctorProfiles()->exists()) {
            return false;
        }

        return (bool) $hospital->delete();
    }
}