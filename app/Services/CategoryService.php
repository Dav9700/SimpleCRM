<?php

namespace App\Services;

use App\Models\Categories;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get all categories with optional pagination
     */
    public function getAll(?int $perPage = null): Collection|LengthAwarePaginator
    {
        return $this->categoryRepository->all($perPage);
    }

    /**
     * Get active categories only
     */
    public function getActive(?int $perPage = null): Collection|LengthAwarePaginator
    {
        return $this->categoryRepository->getActive($perPage);
    }

    /**
     * Get a single category by ID
     */
    public function getById(int $id): Categories
    {
        return $this->categoryRepository->findOrFail($id);
    }

    /**
     * Create a new category
     */
    public function create(array $data): Categories
    {
        return $this->categoryRepository->create($data);
    }

    /**
     * Update an existing category
     */
    public function update(Categories $category, array $data): Categories
    {
        return $this->categoryRepository->update($category, $data);
    }

    /**
     * Delete a category
     */
    public function delete(Categories $category): bool
    {
        return $this->categoryRepository->delete($category);
    }

    /**
     * Attach products to a category
     */
    public function attachProducts(Categories $category, array $productIds): void
    {
        $category->products()->attach($productIds);
    }

    /**
     * Detach products from a category
     */
    public function detachProducts(Categories $category, array $productIds): void
    {
        $category->products()->detach($productIds);
    }

    /**
     * Sync products for a category
     */
    public function syncProducts(Categories $category, array $productIds): void
    {
        $category->products()->sync($productIds);
    }
}

