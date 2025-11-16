<?php

namespace App\Services;

use App\Models\Products;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get all products with optional pagination
     */
    public function getAll(?int $perPage = null): Collection|LengthAwarePaginator
    {
        return $this->productRepository->all($perPage);
    }

    /**
     * Get active products only
     */
    public function getActive(?int $perPage = null): Collection|LengthAwarePaginator
    {
        return $this->productRepository->getActive($perPage);
    }

    /**
     * Get a single product by ID
     */
    public function getById(int $id): Products
    {
        return $this->productRepository->findOrFail($id);
    }

    /**
     * Create a new product
     */
    public function create(array $data, ?array $categoryIds = null): Products
    {
        $product = $this->productRepository->create($data);

        if ($categoryIds) {
            $product->categories()->attach($categoryIds);
        }

        return $product->load('categories');
    }

    /**
     * Update an existing product
     */
    public function update(Products $product, array $data, ?array $categoryIds = null): Products
    {
        $product = $this->productRepository->update($product, $data);

        if ($categoryIds !== null) {
            $product->categories()->sync($categoryIds);
        }

        return $product->load('categories');
    }

    /**
     * Delete a product
     */
    public function delete(Products $product): bool
    {
        return $this->productRepository->delete($product);
    }

    /**
     * Attach categories to a product
     */
    public function attachCategories(Products $product, array $categoryIds): void
    {
        $product->categories()->attach($categoryIds);
    }

    /**
     * Detach categories from a product
     */
    public function detachCategories(Products $product, array $categoryIds): void
    {
        $product->categories()->detach($categoryIds);
    }

    /**
     * Sync categories for a product
     */
    public function syncCategories(Products $product, array $categoryIds): void
    {
        $product->categories()->sync($categoryIds);
    }
}

