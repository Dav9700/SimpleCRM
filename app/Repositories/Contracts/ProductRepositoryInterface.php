<?php

namespace App\Repositories\Contracts;

use App\Models\Products;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function all(?int $perPage = null): Collection|LengthAwarePaginator;
    
    public function find(int $id): ?Products;
    
    public function findOrFail(int $id): Products;
    
    public function create(array $data): Products;
    
    public function update(Products $product, array $data): Products;
    
    public function delete(Products $product): bool;
    
    public function where(string $column, $value): Collection;
    
    public function with(string|array $relations): Collection|LengthAwarePaginator;
    
    public function getActive(?int $perPage = null): Collection|LengthAwarePaginator;
}

