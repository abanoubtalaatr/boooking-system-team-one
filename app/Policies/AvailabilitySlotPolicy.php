<?php

namespace App\Policies;

use App\Models\AvailabilitySlot;
use App\Models\User;

class AvailabilitySlotPolicy
{
    public function update(User $user, AvailabilitySlot $slot): bool
    {
        return $user->isDoctor() && $user->id === $slot->doctor_id;
    }

    public function delete(User $user, AvailabilitySlot $slot): bool
    {
        return $this->update($user, $slot);
    }
}
