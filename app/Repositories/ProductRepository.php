<?php

namespace App\Repositories;

use App\Models\Products;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    protected Products $model;

    public function __construct(Products $model)
    {
        $this->model = $model;
    }

    /**
     * Get all products
     */
    public function all(?int $perPage = null): Collection|LengthAwarePaginator
    {
        if ($perPage) {
            return $this->model->with('categories')->paginate($perPage);
        }

        return $this->model->with('categories')->get();
    }

    /**
     * Find a product by ID
     */
    public function find(int $id): ?Products
    {
        return $this->model->with('categories')->find($id);
    }

    /**
     * Find a product by ID or fail
     */
    public function findOrFail(int $id): Products
    {
        return $this->model->with('categories')->findOrFail($id);
    }

    /**
     * Create a new product
     */
    public function create(array $data): Products
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing product
     */
    public function update(Products $product, array $data): Products
    {
        $product->update($data);
        return $product->fresh(['categories']);
    }

    /**
     * Delete a product
     */
    public function delete(Products $product): bool
    {
        return $product->delete();
    }

    /**
     * Get products where column equals value
     */
    public function where(string $column, $value): Collection
    {
        return $this->model->where($column, $value)->with('categories')->get();
    }

    /**
     * Get products with eager loaded relations
     */
    public function with(string|array $relations): Collection|LengthAwarePaginator
    {
        return $this->model->with($relations)->get();
    }

    /**
     * Get active products only
     */
    public function getActive(?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = $this->model->where('active', true)->with('categories');

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }
}

