<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favorite;


class FavoriteController extends Controller
{
    public function index() 
    {
        // return all the favorites for the user
        //
    }


    public function store(Request $request) 
    {
        // add a new favorite for the user
    }

    public function destroy(Favorite $favorite) 
    {
        // remove a favorite for the user
    }
}
