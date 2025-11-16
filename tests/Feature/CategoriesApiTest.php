<?php

use App\Models\Categories;
use App\Models\Products;

describe('Categories API', function () {
    
    describe('GET /api/categories', function () {
        
        it('can list all categories', function () {
            Categories::factory()->count(3)->create();
            
            $response = $this->getJson('/api/categories');
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'active',
                            'created_at',
                            'updated_at',
                            'products'
                        ]
                    ]
                ])
                ->assertJson(['success' => true]);
            
            expect($response->json('data'))->toHaveCount(3);
        });

        it('can list categories with pagination', function () {
            Categories::factory()->count(15)->create();
            
            $response = $this->getJson('/api/categories?per_page=10');
            
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

        it('can list only active categories', function () {
            Categories::factory()->create(['active' => true]);
            Categories::factory()->create(['active' => false]);
            
            $response = $this->getJson('/api/categories?active_only=true');
            
            $response->assertStatus(200);
            
            $categories = $response->json('data');
            foreach ($categories as $category) {
                expect($category['active'])->toBeTrue();
            }
        });
    });

    describe('POST /api/categories', function () {
        
        it('can create a category', function () {
            $categoryData = [
                'name' => 'New Category',
                'description' => 'Category description',
                'active' => true
            ];
            
            $response = $this->postJson('/api/categories', $categoryData);
            
            $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'active'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'name' => 'New Category',
                        'description' => 'Category description',
                        'active' => true
                    ]
                ]);
            
            $this->assertDatabaseHas('categories', [
                'name' => 'New Category',
                'description' => 'Category description',
                'active' => true
            ]);
        });

        it('validates required fields when creating a category', function () {
            $response = $this->postJson('/api/categories', []);
            
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
            $response = $this->postJson('/api/categories', [
                'name' => str_repeat('a', 26), // 26 characters
            ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        it('validates description max length', function () {
            $response = $this->postJson('/api/categories', [
                'name' => 'Valid Name',
                'description' => str_repeat('a', 256), // 256 characters
            ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['description']);
        });
    });

    describe('GET /api/categories/{id}', function () {
        
        it('can show a single category', function () {
            $category = Categories::factory()->create();
            
            $response = $this->getJson("/api/categories/{$category->id}");
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'active',
                        'products'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $category->id,
                        'name' => $category->name
                    ]
                ]);
        });

        it('returns 404 for non-existent category', function () {
            $response = $this->getJson('/api/categories/999');
            
            $response->assertStatus(404);
        });

        it('shows category with products', function () {
            $category = Categories::factory()->create();
            $product = Products::factory()->create();
            $product->categories()->attach($category->id);
            
            $response = $this->getJson("/api/categories/{$category->id}");
            
            $response->assertStatus(200);
            expect($response->json('data.products'))->toBeArray();
        });
    });

    describe('PUT/PATCH /api/categories/{id}', function () {
        
        it('can update a category', function () {
            $category = Categories::factory()->create([
                'name' => 'Old Name',
                'description' => 'Old Description'
            ]);
            
            $updateData = [
                'name' => 'Updated Name',
                'description' => 'Updated Description'
            ];
            
            $response = $this->putJson("/api/categories/{$category->id}", $updateData);
            
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'name' => 'Updated Name',
                        'description' => 'Updated Description'
                    ]
                ]);
            
            $this->assertDatabaseHas('categories', [
                'id' => $category->id,
                'name' => 'Updated Name'
            ]);
        });

        it('validates data when updating', function () {
            $category = Categories::factory()->create();
            
            $response = $this->putJson("/api/categories/{$category->id}", [
                'name' => str_repeat('a', 26)
            ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });
    });

    describe('DELETE /api/categories/{id}', function () {
        
        it('can delete a category', function () {
            $category = Categories::factory()->create();
            
            $response = $this->deleteJson("/api/categories/{$category->id}");
            
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Category deleted successfully'
                ]);
            
            $this->assertDatabaseMissing('categories', [
                'id' => $category->id
            ]);
        });

        it('returns 404 when deleting non-existent category', function () {
            $response = $this->deleteJson('/api/categories/999');
            
            $response->assertStatus(404);
        });
    });
});

