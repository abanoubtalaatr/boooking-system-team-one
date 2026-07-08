<?php

namespace App\Services;

use App\Models\User;
use App\Models\Favorite;

class FavoriteService
{
    // TODO: use the authenticated user, instead of the hardcoded user id
    /**
     * Get all favorites for a user
     */
    public function getFavorites() 
    {
        $user = User::findOrFail(1);
        return Favorite::where('user_id', $user->id)->with('doctor')->paginate(10);
    }
    /**
     * Add a favorite for a user
     */
    public function addFavorite($doctorId) 
    {
        $user = User::findOrFail(1);
        $favorite = Favorite::firstOrCreate([
            'user_id' => $user->id,
            'doctor_id' => $doctorId,
        ]);
        return $favorite;
    }

    /**
     * Remove a favorite for a user 
     */
    public function removeFavorite($doctorId) 
    {
        $user = User::findOrFail(1);
        $favorite = Favorite::where('user_id', $user->id)->where('doctor_id', $doctorId)->first();
        if (!$favorite) {
            return false;
        }
        $favorite->delete();
        return true;
    }
}
