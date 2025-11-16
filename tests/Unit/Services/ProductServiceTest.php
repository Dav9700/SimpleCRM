<?php

use App\Models\Products;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ProductService;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;

beforeEach(function () {
	$this->mockRepository = Mockery::mock(ProductRepositoryInterface::class);
	$this->productService = new ProductService($this->mockRepository);
});

afterEach(function () {
	Mockery::close();
});

describe('ProductService', function () {
	it('can get all products', function () {
		$products = new \Illuminate\Database\Eloquent\Collection([
			Mockery::mock(Products::class),
			Mockery::mock(Products::class),
			Mockery::mock(Products::class),
		]);

		$this->mockRepository
			->shouldReceive('all')
			->once()
			->with(null)
			->andReturn($products);

		$result = $this->productService->getAll();

		expect($result)->toBe($products);
	});

	it('can get all products with pagination', function () {
		$perPage = 10;
		$paginatedProducts = new LengthAwarePaginator([], 50, $perPage);

		$this->mockRepository
			->shouldReceive('all')
			->once()
			->with($perPage)
			->andReturn($paginatedProducts);

		$result = $this->productService->getAll($perPage);

		expect($result)->toBe($paginatedProducts);
	});

	it('can get active products', function () {
		$activeProducts = new \Illuminate\Database\Eloquent\Collection([
			Mockery::mock(Products::class),
			Mockery::mock(Products::class),
		]);

		$this->mockRepository
			->shouldReceive('getActive')
			->once()
			->with(null)
			->andReturn($activeProducts);

		$result = $this->productService->getActive();

		expect($result)->toBe($activeProducts);
	});

	it('can get a product by id', function () {
		$product = Mockery::mock(Products::class);

		$this->mockRepository
			->shouldReceive('findOrFail')
			->once()
			->with(1)
			->andReturn($product);

		$result = $this->productService->getById(1);

		expect($result)->toBe($product);
	});

	it('can create a product without categories', function () {
		$productData = [
			'name' => 'Test Product',
			'description' => 'Test Description',
			'active' => true,
		];

		$product = Mockery::mock(Products::class);
		$product->shouldReceive('load')->once()->with('categories')->andReturnSelf();

		$this->mockRepository
			->shouldReceive('create')
			->once()
			->with($productData)
			->andReturn($product);

		$result = $this->productService->create($productData);

		expect($result)->toBe($product);
	});

	it('can create a product with categories', function () {
		$productData = [
			'name' => 'Test Product',
			'description' => 'Test Description',
			'active' => true,
		];
		$categoryIds = [1, 2, 3];

		$product = Mockery::mock(Products::class);
		$product->shouldReceive('categories')->once()->andReturn($relation = Mockery::mock());
		$relation->shouldReceive('attach')->once()->with($categoryIds);
		$product->shouldReceive('load')->once()->with('categories')->andReturnSelf();

		$this->mockRepository
			->shouldReceive('create')
			->once()
			->with($productData)
			->andReturn($product);

		$result = $this->productService->create($productData, $categoryIds);

		expect($result)->toBe($product);
	});

	it('can update a product', function () {
		$product = Mockery::mock(Products::class);
		$updateData = ['name' => 'Updated Product'];

		$this->mockRepository
			->shouldReceive('update')
			->once()
			->with($product, $updateData)
			->andReturn($product);

		$product->shouldReceive('load')->once()->with('categories')->andReturnSelf();

		$result = $this->productService->update($product, $updateData);

		expect($result)->toBe($product);
	});

	it('can update a product with category sync', function () {
		$product = Mockery::mock(Products::class);
		$updateData = ['name' => 'Updated Product'];
		$categoryIds = [2, 3];

		$this->mockRepository
			->shouldReceive('update')
			->once()
			->with($product, $updateData)
			->andReturn($product);

		$product->shouldReceive('categories')->once()->andReturn($relation = Mockery::mock());
		$relation->shouldReceive('sync')->once()->with($categoryIds);
		$product->shouldReceive('load')->once()->with('categories')->andReturnSelf();

		$result = $this->productService->update($product, $updateData, $categoryIds);

		expect($result)->toBe($product);
	});

	it('can delete a product', function () {
		$product = Mockery::mock(Products::class);

		$this->mockRepository
			->shouldReceive('delete')
			->once()
			->with($product)
			->andReturn(true);

		$result = $this->productService->delete($product);

		expect($result)->toBeTrue();
	});

	it('can attach categories to a product', function () {
		$product = Mockery::mock(Products::class);
		$categoryIds = [1, 2];

		$product->shouldReceive('categories')->once()->andReturn($relation = Mockery::mock());
		$relation->shouldReceive('attach')->once()->with($categoryIds);

		$this->productService->attachCategories($product, $categoryIds);
	});

	it('can detach categories from a product', function () {
		$product = Mockery::mock(Products::class);
		$categoryIds = [1, 2];

		$product->shouldReceive('categories')->once()->andReturn($relation = Mockery::mock());
		$relation->shouldReceive('detach')->once()->with($categoryIds);

		$this->productService->detachCategories($product, $categoryIds);
	});

	it('can sync categories for a product', function () {
		$product = Mockery::mock(Products::class);
		$categoryIds = [2, 3];

		$product->shouldReceive('categories')->once()->andReturn($relation = Mockery::mock());
		$relation->shouldReceive('sync')->once()->with($categoryIds);

		$this->productService->syncCategories($product, $categoryIds);
	});
});

