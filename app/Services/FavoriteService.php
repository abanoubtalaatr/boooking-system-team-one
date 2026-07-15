<?php

namespace App\Services;

use App\Models\Favorite;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FavoriteService
{
    // TODO: use the authenticated user, instead of the hardcoded user id
    /**
     * Get all favorites for a user
     */
    public function getFavorites(int $patientId): LengthAwarePaginator
    {
        return Favorite::where('user_id', $patientId)->with('doctor')->paginate(10);
    }

    /**
     * Add a favorite for a user
     */
    public function addFavorite(int $patientId, int $doctorId): Favorite
    {
        return Favorite::firstOrCreate([
            'user_id' => $patientId,
            'doctor_id' => $doctorId,
        ]);
    }

    /**
     * Remove a favorite for a user
     */
    public function removeFavorite(int $patientId, int $doctorId): bool
    {
        $favorite = Favorite::where('user_id', $patientId)->where('doctor_id', $doctorId)->first();
        if (! $favorite) {
            return false;
        }

        $favorite->delete();

        return true;
    }
}
