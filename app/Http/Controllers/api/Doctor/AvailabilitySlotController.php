<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreAvailabilitySlotRequest;
use App\Http\Requests\Doctor\UpdateAvailabilitySlotRequest;
use App\Http\Resources\AvailabilitySlotResource;
use App\Models\AvailabilitySlot;
use App\Repositories\Contracts\DoctorProfileRepositoryInterface;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class AvailabilitySlotController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DoctorService $doctors,
        private readonly DoctorProfileRepositoryInterface $doctorProfiles,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $profile = $this->doctorProfiles->findByUserId($request->user()->id);
        $slots = $this->doctors->paginateAvailabilitySlots($profile->id);

        return $this->apiResponse(AvailabilitySlotResource::collection($slots));
    }

    public function store(StoreAvailabilitySlotRequest $request): JsonResponse
    {
        $profile = $this->doctorProfiles->findByUserId($request->user()->id);
        $slot = $this->doctors->createAvailabilitySlot($profile, $request->validated());

        return $this->apiResponse(new AvailabilitySlotResource($slot), "Slot created.", 201);
    }

    public function show(AvailabilitySlot $availabilitySlot): JsonResponse
    {
        $this->authorize("update", $availabilitySlot);

        return $this->apiResponse(new AvailabilitySlotResource($availabilitySlot));
    }

    public function update(UpdateAvailabilitySlotRequest $request, AvailabilitySlot $availabilitySlot): JsonResponse
    {
        $slot = $this->doctors->updateAvailabilitySlot($availabilitySlot, $request->validated());

        return $this->apiResponse(new AvailabilitySlotResource($slot));
    }

    public function destroy(AvailabilitySlot $availabilitySlot): JsonResponse
    {
        $this->authorize("delete", $availabilitySlot);
        $this->doctors->deleteAvailabilitySlot($availabilitySlot);

        return $this->apiResponse(null, "Slot deleted.");
    }
}
