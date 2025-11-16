<?php

namespace App\Repositories;

use App\Models\Categories;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository implements CategoryRepositoryInterface
{
    protected Categories $model;

    public function __construct(Categories $model)
    {
        $this->model = $model;
    }

    /**
     * Get all categories
     */
    public function all(?int $perPage = null): Collection|LengthAwarePaginator
    {
        if ($perPage) {
            return $this->model->with('products')->paginate($perPage);
        }

        return $this->model->with('products')->get();
    }

    /**
     * Find a category by ID
     */
    public function find(int $id): ?Categories
    {
        return $this->model->with('products')->find($id);
    }

    /**
     * Find a category by ID or fail
     */
    public function findOrFail(int $id): Categories
    {
        return $this->model->with('products')->findOrFail($id);
    }

    /**
     * Create a new category
     */
    public function create(array $data): Categories
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing category
     */
    public function update(Categories $category, array $data): Categories
    {
        $category->update($data);
        return $category->fresh(['products']);
    }

    /**
     * Delete a category
     */
    public function delete(Categories $category): bool
    {
        return $category->delete();
    }

    /**
     * Get categories where column equals value
     */
    public function where(string $column, $value): Collection
    {
        return $this->model->where($column, $value)->with('products')->get();
    }

    /**
     * Get categories with eager loaded relations
     */
    public function with(string|array $relations): Collection|LengthAwarePaginator
    {
        return $this->model->with($relations)->get();
    }

    /**
     * Get active categories only
     */
    public function getActive(?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = $this->model->where('active', true)->with('products');

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }
}

