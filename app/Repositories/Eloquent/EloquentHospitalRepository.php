<?php

namespace App\Repositories\Eloquent;

use App\Models\Hospital;
use App\Repositories\Contracts\HospitalRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentHospitalRepository implements HospitalRepositoryInterface
{
    public function find(string $id): ?Hospital
    {
        return Hospital::find($id);
    }

    public function create(array $data): Hospital
    {
        return Hospital::create($data);
    }

    public function update(Hospital $hospital, array $data): Hospital
    {
        $hospital->update($data);

        return $hospital->refresh();
    }

    public function delete(Hospital $hospital): bool
    {
        return (bool) $hospital->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Hospital::orderBy("name")->paginate($perPage);
    }

    public function findManyByIds(array $ids): Collection
    {
        return Hospital::whereIn("id", $ids)->get();
    }
}
