<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AvailabilitySlotResource;
use App\Services\AvailabilitySlotService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AvailabilitySlotController extends Controller
{
    public function __construct(
        protected AvailabilitySlotService $slotService
    ) {}

    /**
     * المريض بيشوف المواعيد المتاحة لدكتور معين.
     * GET /doctors/{doctor}/availability-slots
     */
    public function index(int $doctor, Request $request): AnonymousResourceCollection
    {
        return AvailabilitySlotResource::collection(
            $this->slotService->listForDoctor($doctor, $request)
        );
    }

    /**
     * GET /availability-slots/{id}
     */
    public function show(int $id): AvailabilitySlotResource
    {
        return new AvailabilitySlotResource(
            $this->slotService->show($id)
        );
    }
}