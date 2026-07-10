<?php

namespace App\Repositories;

use App\Models\DoctorProfile;
use App\Models\Favorite;
use App\Models\Patient;
use App\Repositories\Contracts\DoctorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DoctorRepository implements DoctorRepositoryInterface
{
    public function paginate(Request $request, ?Patient $patient = null): LengthAwarePaginator
    {
        $query = DoctorProfile::query()
            ->with(['user', 'specialty', 'hospital'])
            ->withAvg('reviews as rating_avg', 'rating')
            ->withCount('reviews as reviews_count')
            ->where('is_active', true)
            ->filter($request);

        if ($patient) {
            $query->withExists([
                'favorites as is_favorite' => function ($q) use ($patient) {
                    $q->where('user_id', $patient->id);
                }
            ]);
        }

        $query = $this->applyDistanceSelect($query, $patient, $request);
        $query = $this->applySort($query, $request);

        $perPage = (int) $request->input('per_page', 15);

        return $query->paginate($perPage)->appends($request->query());
    }

    public function findById(int $id, Request $request, ?Patient $patient = null): ?DoctorProfile
    {
        $query = DoctorProfile::query()
            ->with([
                'user', 'specialty', 'hospital',
                'reviews' => fn ($q) => $q->latest()->limit(20),
                'reviews.patient',
                'availabilitySlots' => fn ($q) => $q
                    ->where('is_booked', false)
                    ->where(function ($q2) {
                        $q2->whereDate('day', '>', now()->toDateString())
                           ->orWhere(function ($q3) {
                               $q3->whereDate('day', now()->toDateString())
                                  ->whereTime('start_time', '>=', now()->toTimeString());
                           });
                    })
                    ->orderBy('day')->orderBy('start_time'),
            ])
            ->withAvg('reviews as rating_avg', 'rating')
            ->withCount('reviews as reviews_count');

        $query = $this->applyDistanceSelect($query, $patient, $request);

        $doctor = $query->find($id);


        if ($doctor) {
            $doctor->is_favorite = $this->isFavorite($doctor->user_id, $patient);
        }

        return $doctor;
    }

    protected function applyDistanceSelect(Builder $query, ?Patient $patient, Request $request): Builder
    {
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        if ((!$lat || !$lng) && $patient) {
            $lat = $lat ?? $patient->latitude;
            $lng = $lng ?? $patient->longitude;
        }

        if (!$lat || !$lng) {
            return $query;
        }

        $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(doctor_profiles.latitude))
            * cos(radians(doctor_profiles.longitude) - radians(?))
            + sin(radians(?)) * sin(radians(doctor_profiles.latitude))))";

        return $query->selectRaw("doctor_profiles.*, {$haversine} AS distance", [$lat, $lng, $lat]);
    }

    protected function applySort(Builder $query, Request $request): Builder
    {
        $sort = $request->input('sort');
        $hasDistance = str_contains((string) $query->toSql(), 'AS distance');

        return match ($sort) {
            'rating'     => $query->orderByDesc('rating_avg'),
            'experience' => $query->orderByDesc('experience_years'),
            'price_low'  => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            'nearest'    => $hasDistance ? $query->orderBy('distance') : $query->orderByDesc('created_at'),
            default      => $hasDistance ? $query->orderBy('distance') : $query->orderByDesc('created_at'),
        };
    }

    protected function isFavorite(int $doctorUserId, ?Patient $patient): bool
    {
       // dd($doctorUserId, $patient->id);
        if (!$patient) {
            return false;
        }

        return Favorite::query()
            ->where('doctor_id', $doctorUserId)
            ->where('user_id', $patient->id)
            ->exists();
    }
}