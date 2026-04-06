<?php

declare(strict_types=1);

namespace App\Abstracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    public function newQuery(): Builder;

    public function all(array $columns = ['*'], array $relations = []): Collection;

    public function find(int|string $id, array $columns = ['*'], array $relations = []): Model|Collection|null;

    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Model|Collection|null;

    public function findBy(string $field, mixed $value, array $columns = ['*'], array $relations = []): Collection;

    public function findOneBy(string $field, mixed $value, array $columns = ['*'], array $relations = []): ?Model;

    public function findByFields(array $fields, array $columns = ['*'], array $relations = []): Collection;

    public function findOneByFields(array $fields, array $columns = ['*'], array $relations = []): ?Model;

    public function create(array $data): Model;

    public function update(int|string $id, array $data): Model|Collection|null;

    public function delete(int|string $id): bool;

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator;

    public function count(array $conditions = []): int;

    public function exists(array $conditions): bool;

    public function getModel();
}
