<?php

namespace App\Policies;

use App\Models\SearchHistory;
use App\Models\User;

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

    /**
     * Delete all search history for the authenticated user
     */
    public function deleteAny(User $user): bool
    {
        return (bool) $user->id;
    }
}
