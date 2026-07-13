<?php

namespace App\Repositories\Eloquent;

use App\Models\Specialty;
use App\Repositories\Contracts\SpecialtyRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentSpecialtyRepository implements SpecialtyRepositoryInterface
{
    public function find(string $id): ?Specialty
    {
        return Specialty::find($id);
    }

    public function create(array $data): Specialty
    {
        return Specialty::create($data);
    }

    public function update(Specialty $specialty, array $data): Specialty
    {
        $specialty->update($data);

        return $specialty->refresh();
    }

    public function delete(Specialty $specialty): bool
    {
        return (bool) $specialty->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Specialty::orderBy("name")->paginate($perPage);
    }

    public function findManyByIds(array $ids): Collection
    {
        return Specialty::whereIn("id", $ids)->get();
    }
}
