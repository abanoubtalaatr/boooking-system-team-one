<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SearchHistory;
use Illuminate\Support\Facades\Auth;

// for authorization
class SearchHistoryPolicy
{

    /**
     * Get all search history
     */
    public function viewAny(User $user)
    {
        return $user->id;
    }


    /**
     * Delete a search history
     */
    public function delete(User $user, SearchHistory $searchHistory)
    {
        return $searchHistory->user_id === $user->id;
    }
}
