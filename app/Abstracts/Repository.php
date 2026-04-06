<?php

declare(strict_types=1);

namespace App\Abstracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class Repository implements RepositoryInterface
{
    /**
     * The model instance.
     */
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Get all records.
     */
    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->newQuery()
            ->with($relations)
            ->get($columns);
    }

    /**
     * Find a record by ID.
     */
    public function find(int|string $id, array $columns = ['*'], array $relations = []): Model|Collection|null
    {
        return $this->newQuery()
            ->with($relations)
            ->find($id, $columns);
    }

    /**
     * Find a record by ID or fail.
     */
    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Model|Collection|null
    {
        return $this->newQuery()
            ->with($relations)
            ->findOrFail($id, $columns);
    }

    /**
     * Find records by field.
     */
    public function findBy(string $field, mixed $value, array $columns = ['*'], array $relations = []): Collection
    {
        return $this->newQuery()
            ->with($relations)
            ->where($field, $value)
            ->get($columns);
    }

    /**
     * Find first record by field.
     */
    public function findOneBy(string $field, mixed $value, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->newQuery()
            ->with($relations)
            ->where($field, $value)
            ->first($columns);
    }

    /**
     * Find records by multiple fields.
     */
    public function findByFields(array $fields, array $columns = ['*'], array $relations = []): Collection
    {
        $query = $this->newQuery()->with($relations);

        foreach ($fields as $field => $value) {
            $query->where($field, $value);
        }

        return $query->get($columns);
    }

    /**
     * Find first record by multiple fields.
     */
    public function findOneByFields(array $fields, array $columns = ['*'], array $relations = []): ?Model
    {
        $query = $this->newQuery()->with($relations);

        foreach ($fields as $field => $value) {
            $query->where($field, $value);
        }

        return $query->first($columns);
    }

    /**
     * Create a new record.
     */
    public function create(array $data): Model
    {
        return $this->newQuery()->create($data);
    }

    /**
     * Update a record.
     */
    public function update(int|string $id, array $data): Model|Collection|null
    {
        $record = $this->findOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    /**
     * Delete a record.
     */
    public function delete(int|string $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get paginated records.
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with($relations)
            ->paginate($perPage, $columns);
    }

    /**
     * Count records.
     */
    public function count(array $conditions = []): int
    {
        $query = $this->newQuery();

        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }

        return $query->count();
    }

    /**
     * Check if record exists.
     */
    public function exists(array $conditions): bool
    {
        $query = $this->newQuery();

        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }

        return $query->exists();
    }

    /**
     * Get the underlying model instance.
     */
    public function getModel()
    {
        return $this->model;
    }
}
