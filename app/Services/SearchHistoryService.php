<?php

namespace App\Services;

use App\Models\SearchHistory;
use Illuminate\Support\Facades\Auth;

class SearchHistoryService
{
    /**
     * Get search history
     */
    public function getSearchHistory(?string $source = null)
    {
        $user = Auth::user();

        return SearchHistory::where('user_id', $user->id)->when($source, function ($query) use ($source) {
            $query->where('source', $source);
        })->latest()
            ->paginate(10);
    }

    /**
     * Delete search history
     */
    public function deleteSearchHistory(int $id)
    {
        $user = Auth::user();

        $searchHistory = SearchHistory::where('user_id', $user->id)
            ->where('id', $id)->first();

        if (! $searchHistory) {
            throw new \Exception('Search history not found');
        }
        $searchHistory->delete();

        return true;
    }

    /**
     * Add Search History
     */
    public function recordSearchHistory(string $query, string $source): SearchHistory
    {
        $user = Auth::user();

        return SearchHistory::updateOrCreate(
            [
                'user_id' => $user->id,
                'query' => $query,
                'source' => $source,
            ],
            [
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Delete all search history
     */
    public function deleteAllSearchHistory(?string $source = null): int
    {
        $user = Auth::user();

        return SearchHistory::query()
            ->where('user_id', $user->id)
            ->when($source, function ($query) use ($source) {
                $query->where('source', $source);
            })
            ->delete();
    }
}
