<?php

namespace App\Http\Controllers\Api\Faq;

use App\Http\Controllers\Controller;

class FaqController extends Controller
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
