<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
