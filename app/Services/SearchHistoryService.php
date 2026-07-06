<?php

namespace App\Services;

use App\Models\SearchHistory;

use App\Models\User;
class SearchHistoryService
{

    /**
     * Get search history
     */
    public function getSearchHistory(User $user)
    {
        return SearchHistory::where('user_id', $user->id)->latest()
            ->paginate(10);
    }

    /**
     * Delete search history
     */
    public function deleteSearchHistory(User $user, $id)
    {
        $searchHistory = SearchHistory::where('user_id', $user->id)->findOrFail($id);
        $searchHistory->delete();
        return true;
    }

    /** 
     * Add Search History
     */
    public function recordSearchHistory(User $user, $doctoryId): SearchHistory
    {
        // check if the search history already exist
        $searchHistory = SearchHistory::where('user_id', $user->id)
            ->where('doctor_id', $doctoryId)
            ->first();
        
        // if the search history already exist, return true
        if ($searchHistory) {
            $searchHistory->touch();
            return $searchHistory;
        }

        // if the search history does not exist, create a new search history
        $searchHistory = SearchHistory::create([
            'user_id' => $user->id,
            'doctor_id' => $doctoryId,
        ]); 
        
        return $searchHistory;
    }
}
