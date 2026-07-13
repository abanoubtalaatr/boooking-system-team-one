<?php

namespace App\Repositories\Contracts;

use App\Models\Hospital;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface HospitalRepositoryInterface
{
    public function find(string $id): ?Hospital;

    public function create(array $data): Hospital;

    public function update(Hospital $hospital, array $data): Hospital;

    public function delete(Hospital $hospital): bool;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findManyByIds(array $ids): Collection;
}
