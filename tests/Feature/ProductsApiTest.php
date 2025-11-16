<?php

use App\Models\Categories;
use App\Models\Products;

describe('Products API', function () {
    
    beforeEach(function () {
        // Create some test data
        $this->category = Categories::factory()->create([
            'name' => 'Electronics',
            'description' => 'Electronic products',
            'active' => true,
        ]);
    });

    describe('GET /api/products', function () {
        
        it('can list all products', function () {
            Products::factory()->count(3)->create();
            
            $response = $this->getJson('/api/products');
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'active',
                            'picture',
                            'created_at',
                            'updated_at',
                            'categories'
                        ]
                    ]
                ])
                ->assertJson(['success' => true]);
            
            expect($response->json('data'))->toHaveCount(3);
        });

        it('can list products with pagination', function () {
            Products::factory()->count(15)->create();
            
            $response = $this->getJson('/api/products?per_page=10');
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data',
                        'current_page',
                        'per_page',
                        'total'
                    ]
                ]);
            
            expect($response->json('data.data'))->toHaveCount(10);
        });

        it('can list only active products', function () {
            Products::factory()->create(['active' => true]);
            Products::factory()->create(['active' => false]);
            
            $response = $this->getJson('/api/products?active_only=true');
            
            $response->assertStatus(200);
            
            $products = $response->json('data');
            foreach ($products as $product) {
                expect($product['active'])->toBeTrue();
            }
        });
    });

    describe('POST /api/products', function () {
        
        it('can create a product', function () {
            $productData = [
                'name' => 'New Product',
                'description' => 'Product description',
                'active' => true,
                'picture' => 'product.jpg'
            ];
            
            $response = $this->postJson('/api/products', $productData);
            
            $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'active',
                        'picture'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'name' => 'New Product',
                        'description' => 'Product description',
                        'active' => true
                    ]
                ]);
            
            $this->assertDatabaseHas('products', [
                'name' => 'New Product',
                'description' => 'Product description',
                'active' => true
            ]);
        });

        it('can create a product with categories', function () {
            $category2 = Categories::factory()->create();
            
            $productData = [
                'name' => 'Product with Categories',
                'description' => 'Description',
                'category_ids' => [$this->category->id, $category2->id]
            ];
            
            $response = $this->postJson('/api/products', $productData);
            
            $response->assertStatus(201);
            
            $product = Products::where('name', 'Product with Categories')->first();
            expect($product->categories)->toHaveCount(2);
        });

        it('validates required fields when creating a product', function () {
            $response = $this->postJson('/api/products', []);
            
            $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors' => [
                        'name'
                    ]
                ])
                ->assertJson(['success' => false]);
        });

        it('validates name max length', function () {
            $response = $this->postJson('/api/products', [
                'name' => str_repeat('a', 26), // 26 characters
            ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        it('validates description max length', function () {
            $response = $this->postJson('/api/products', [
                'name' => 'Valid Name',
                'description' => str_repeat('a', 256), // 256 characters
            ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['description']);
        });

        it('validates category_ids exist', function () {
            $response = $this->postJson('/api/products', [
                'name' => 'Valid Name',
                'category_ids' => [999, 998] // Non-existent IDs
            ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['category_ids.0', 'category_ids.1']);
        });
    });

    describe('GET /api/products/{id}', function () {
        
        it('can show a single product', function () {
            $product = Products::factory()->create();
            
            $response = $this->getJson("/api/products/{$product->id}");
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'active',
                        'picture',
                        'categories'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $product->id,
                        'name' => $product->name
                    ]
                ]);
        });

        it('returns 404 for non-existent product', function () {
            $response = $this->getJson('/api/products/999');
            
            $response->assertStatus(404);
        });

        it('shows product with categories', function () {
            $product = Products::factory()->create();
            $product->categories()->attach($this->category->id);
            
            $response = $this->getJson("/api/products/{$product->id}");
            
            $response->assertStatus(200);
            expect($response->json('data.categories'))->toBeArray();
        });
    });

    describe('PUT/PATCH /api/products/{id}', function () {
        
        it('can update a product', function () {
            $product = Products::factory()->create([
                'name' => 'Old Name',
                'description' => 'Old Description'
            ]);
            
            $updateData = [
                'name' => 'Updated Name',
                'description' => 'Updated Description'
            ];
            
            $response = $this->putJson("/api/products/{$product->id}", $updateData);
            
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'name' => 'Updated Name',
                        'description' => 'Updated Description'
                    ]
                ]);
            
            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'name' => 'Updated Name'
            ]);
        });

        it('can update product categories', function () {
            $category2 = Categories::factory()->create();
            $product = Products::factory()->create();
            $product->categories()->attach($this->category->id);
            
            $response = $this->putJson("/api/products/{$product->id}", [
                'category_ids' => [$category2->id]
            ]);
            
            $response->assertStatus(200);
            
            $product->refresh();
            expect($product->categories->pluck('id')->toArray())->toContain($category2->id);
            expect($product->categories->pluck('id')->toArray())->not->toContain($this->category->id);
        });

        it('validates data when updating', function () {
            $product = Products::factory()->create();
            
            $response = $this->putJson("/api/products/{$product->id}", [
                'name' => str_repeat('a', 26)
            ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });
    });

    describe('DELETE /api/products/{id}', function () {
        
        it('can delete a product', function () {
            $product = Products::factory()->create();
            
            $response = $this->deleteJson("/api/products/{$product->id}");
            
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Product deleted successfully'
                ]);
            
            $this->assertDatabaseMissing('products', [
                'id' => $product->id
            ]);
        });

        it('returns 404 when deleting non-existent product', function () {
            $response = $this->deleteJson('/api/products/999');
            
            $response->assertStatus(404);
        });
    });
});

