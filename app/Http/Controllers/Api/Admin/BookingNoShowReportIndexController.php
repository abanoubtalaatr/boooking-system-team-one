<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingNoShowReportResource;
use App\Models\BookingNoShowReport;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingNoShowReportIndexController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        return BookingNoShowReportResource::collection(
            BookingNoShowReport::query()
                ->with(['booking', 'doctor', 'reviewer'])
                ->latest()
                ->paginate(20),
        );
    }
}
