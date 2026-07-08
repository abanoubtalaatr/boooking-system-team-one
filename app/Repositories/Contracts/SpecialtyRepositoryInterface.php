<?php

namespace App\Repositories\Contracts;

use App\Models\Specialty;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SpecialtyRepositoryInterface
{
    public function find(string $id): ?Specialty;

    public function create(array $data): Specialty;

    public function update(Specialty $specialty, array $data): Specialty;

    public function delete(Specialty $specialty): bool;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findManyByIds(array $ids): Collection;
}
