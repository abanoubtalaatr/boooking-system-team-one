<?php

namespace App\Http\Controllers;

use App\Models\DoctorProfile;
use App\Models\FaqCategory;
use App\Models\Promotion;
use App\Models\Specialization;
use Illuminate\Http\Request;

class HomeController extends Controller
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
