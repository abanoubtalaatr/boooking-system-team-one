<?php

namespace App\Http\Controllers\Api\Policy;

use App\Http\Controllers\Controller;

class PolicyController extends Controller
{
    public function index()
    {
        $test = 'Hello';

        return response()->json([
            'message' => 'Welcome to the API',
            'test' => $test,
        ]);
    }
}
