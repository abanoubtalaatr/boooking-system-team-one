<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DeleteAllSearchHistoryRequest;
use App\Http\Resources\SearchHistoryCollection;
use App\Models\SearchHistory;
use App\Services\SearchHistoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class SearchHistoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private SearchHistoryService $searchHistoryService) {}

    /**
     * Get all search history
     */
    public function index(): SearchHistoryCollection
    {
        $source = request()->query('source');
        // $this->authorize('viewAny', SearchHistory::class);
        $searchHistory = $this->searchHistoryService->getSearchHistory($source ?? null);

        return new SearchHistoryCollection($searchHistory);
    }

    /**
     * Delete a search history item
     */
    public function destroy(SearchHistory $searchHistory): JsonResponse
    {
        $this->authorize('delete', $searchHistory);
        $this->searchHistoryService->deleteSearchHistory($searchHistory->id);

        return response()->json(['message' => 'Search history deleted successfully']);
    }

    /**
     * Delete all search history, optionally scoped by source
     */
    public function destroyAll(DeleteAllSearchHistoryRequest $request): JsonResponse
    {
        $this->authorize('deleteAny', SearchHistory::class);

        $source = $request->validated('source');
        $deletedCount = $this->searchHistoryService->deleteAllSearchHistory($source);

        $message = $source
            ? "Search history for source '{$source}' cleared successfully"
            : 'All search history cleared successfully';

        return response()->json([
            'message' => $message,
            'deleted_count' => $deletedCount,
        ]);
    }
}
