<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateFavoriteRequest;
use App\Http\Requests\Api\DeleteFavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Models\Patient;
use App\Services\FavoriteService;

class FavoriteController extends Controller
{
    public function __construct(private FavoriteService $favoriteService) {}

    /**
     * Get all favorites for the user
     */
    public function index()
    {
        /** @var Patient $patient */
        $patient = request()->user('patient');
        $favorites = $this->favoriteService->getFavorites($patient->id);

        return FavoriteResource::collection($favorites);
    }

    /**
     * Add a new favorite for the user
     */
    public function store(CreateFavoriteRequest $request)
    {
        /** @var Patient $patient */
        $patient = $request->user('patient');
        $this->favoriteService->addFavorite($patient->id, $request->integer('doctor_id'));

        return response()->json(['message' => 'Favorite added successfully']);
    }

    /**
     * Remove a favorite for the user
     */
    public function destroy(DeleteFavoriteRequest $request)
    {
        /** @var Patient $patient */
        $patient = $request->user('patient');
        $isDeleted = $this->favoriteService->removeFavorite($patient->id, $request->integer('doctor_id'));
        $message = $isDeleted ? 'Favorite removed successfully' : 'Favorite not found';

        return response()->json([
            'message' => $message,
        ], $isDeleted ? 200 : 404);
    }
}
