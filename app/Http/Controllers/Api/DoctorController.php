<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Services\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoctorController extends Controller
{
    public function __construct(
        protected DoctorService $doctorService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return DoctorResource::collection(
            $this->doctorService->list($request)
        );
    }
  

    public function show(int $id, Request $request): DoctorResource
    {
        return new DoctorResource(
            $this->doctorService->show($id, $request)
        );
    }
    
}