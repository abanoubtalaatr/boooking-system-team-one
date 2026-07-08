<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DeleteFavoriteRequest;
use App\Http\Requests\Api\CreateFavoriteRequest;
use App\Http\Resources\FavoriteResource;    
use App\Services\FavoriteService;


class FavoriteController extends Controller
{

    public function __construct(private FavoriteService $favoriteService)
    {
    }

    /**
     * Get all favorites for the user
     */
    public function index()
    {
        $favorites = $this->favoriteService->getFavorites();

        return FavoriteResource::collection($favorites);
    }

    /**
     * Add a new favorite for the user
     */
    public function store(CreateFavoriteRequest $request) 
    {
        $this->favoriteService->addFavorite($request->doctor_id);
        return response()->json(['message' => 'Favorite added successfully']);
    }

    /**
     * Remove a favorite for the user
     */
    public function destroy(DeleteFavoriteRequest $request) 
    {
        $isDeleted = $this->favoriteService->removeFavorite($request->doctor_id);
        $message = $isDeleted ? 'Favorite removed successfully' : 'Favorite not found';
        return response()->json([
            'message' => $message,
        ], $isDeleted ? 200 : 404);
    }
}
