<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Home\HomeIndexRequest;
use App\Services\HomeService;
use App\Http\Resources\HomeResource;

class HomeController extends Controller
{

    public function __construct(private readonly HomeService $homeService,) {}

    public function index(HomeIndexRequest $request): HomeResource
    {
        return new HomeResource(
            $this->homeService->index($request)
        );
    }

}
