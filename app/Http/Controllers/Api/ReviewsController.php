<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Http\Requests\ReviewRequest;
use App\Http\Requests\ReviewUpdateRequest;
use App\Models\Patient;
use App\Models\User;

class ReviewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = Review::all();
        return response()->json([
            'status' => true,
            'message' => 'Reviews retrieved successfully',
            'data' => $reviews
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReviewRequest $request)
    {
        $review = Review::create($request->validated());
        return response()->json([
            'status' => true,
            'message' => 'Review created successfully',
            'data' => $review
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $review = Review::findOrFail($id);
        return response()->json([
            'status' => true,
            'message' => 'Review retrieved successfully',
            'data' => $review
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReviewUpdateRequest $request, string $id)
    {
        $review = Review::findOrFail($id);
        $review->update($request->validated());
        return response()->json([
            'status' => true,
            'message' => 'Review updated successfully',
            'data' => $review
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        return response()->json([
            'status' => true,
            'message' => 'Review deleted successfully'
        ]);
    }
}
