<?php

namespace App\Repositories\Contracts;

use App\Models\Categories;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    public function all(?int $perPage = null): Collection|LengthAwarePaginator;
    
    public function find(int $id): ?Categories;
    
    public function findOrFail(int $id): Categories;
    
    public function create(array $data): Categories;
    
    public function update(Categories $category, array $data): Categories;
    
    public function delete(Categories $category): bool;
    
    public function where(string $column, $value): Collection;
    
    public function with(string|array $relations): Collection|LengthAwarePaginator;
    
    public function getActive(?int $perPage = null): Collection|LengthAwarePaginator;
}

