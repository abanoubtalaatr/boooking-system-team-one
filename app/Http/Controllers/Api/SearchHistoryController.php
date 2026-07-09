<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SearchHistory;
use App\Services\SearchHistoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Resources\SearchHistoryCollection;

class SearchHistoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private SearchHistoryService $searchHistoryService)
    {
    }


    /**
     * Get all search history
     */
    public function index() 
    {
        $source = request()->query('source');
        // $this->authorize('viewAny', SearchHistory::class);
        $searchHistory = $this->searchHistoryService->getSearchHistory($source ?? null); 
        return new SearchHistoryCollection($searchHistory);

    }


    /**
     * Delete a search history
     */
    public function destroy(SearchHistory $searchHistory) 
    {
        $this->authorize('delete', $searchHistory);
        $searchHistory = $this->searchHistoryService->deleteSearchHistory( $searchHistory->id); 
        return response()->json(['message' => 'Search history deleted successfully']);
    }

    
}
