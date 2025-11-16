<?php

use App\Models\Categories;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Services\CategoryService;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;

beforeEach(function () {
	$this->mockRepository = Mockery::mock(CategoryRepositoryInterface::class);
	$this->categoryService = new CategoryService($this->mockRepository);
});

afterEach(function () {
	Mockery::close();
});

describe('CategoryService', function () {
	it('can get all categories', function () {
		$categories = new \Illuminate\Database\Eloquent\Collection([
			Mockery::mock(Categories::class),
			Mockery::mock(Categories::class),
			Mockery::mock(Categories::class),
		]);

		$this->mockRepository
			->shouldReceive('all')
			->once()
			->with(null)
			->andReturn($categories);

		$result = $this->categoryService->getAll();

		expect($result)->toBe($categories);
	});

	it('can get all categories with pagination', function () {
		$perPage = 10;
		$paginated = new LengthAwarePaginator([], 50, $perPage);

		$this->mockRepository
			->shouldReceive('all')
			->once()
			->with($perPage)
			->andReturn($paginated);

		$result = $this->categoryService->getAll($perPage);

		expect($result)->toBe($paginated);
	});

	it('can get active categories', function () {
		$active = new \Illuminate\Database\Eloquent\Collection([
			Mockery::mock(Categories::class),
			Mockery::mock(Categories::class),
		]);

		$this->mockRepository
			->shouldReceive('getActive')
			->once()
			->with(null)
			->andReturn($active);

		$result = $this->categoryService->getActive();

		expect($result)->toBe($active);
	});

	it('can get a category by id', function () {
		$category = Mockery::mock(Categories::class);

		$this->mockRepository
			->shouldReceive('findOrFail')
			->once()
			->with(1)
			->andReturn($category);

		$result = $this->categoryService->getById(1);

		expect($result)->toBe($category);
	});

	it('can create a category', function () {
		$data = [
			'name' => 'Test Category',
			'description' => 'Test Description',
			'active' => true,
		];

		$category = Mockery::mock(Categories::class);

		$this->mockRepository
			->shouldReceive('create')
			->once()
			->with($data)
			->andReturn($category);

		$result = $this->categoryService->create($data);

		expect($result)->toBe($category);
	});

	it('can update a category', function () {
		$category = Mockery::mock(Categories::class);
		$updateData = ['name' => 'Updated Category'];

		$this->mockRepository
			->shouldReceive('update')
			->once()
			->with($category, $updateData)
			->andReturn($category);

		$result = $this->categoryService->update($category, $updateData);

		expect($result)->toBe($category);
	});

	it('can delete a category', function () {
		$category = Mockery::mock(Categories::class);

		$this->mockRepository
			->shouldReceive('delete')
			->once()
			->with($category)
			->andReturn(true);

		$result = $this->categoryService->delete($category);

		expect($result)->toBeTrue();
	});

	it('can attach products to a category', function () {
		$category = Mockery::mock(Categories::class);
		$productIds = [1, 2];

		$category->shouldReceive('products')->once()->andReturn($relation = Mockery::mock());
		$relation->shouldReceive('attach')->once()->with($productIds);

		$this->categoryService->attachProducts($category, $productIds);
	});

	it('can detach products from a category', function () {
		$category = Mockery::mock(Categories::class);
		$productIds = [1, 2];

		$category->shouldReceive('products')->once()->andReturn($relation = Mockery::mock());
		$relation->shouldReceive('detach')->once()->with($productIds);

		$this->categoryService->detachProducts($category, $productIds);
	});

	it('can sync products for a category', function () {
		$category = Mockery::mock(Categories::class);
		$productIds = [2, 3];

		$category->shouldReceive('products')->once()->andReturn($relation = Mockery::mock());
		$relation->shouldReceive('sync')->once()->with($productIds);

		$this->categoryService->syncProducts($category, $productIds);
	});
});

