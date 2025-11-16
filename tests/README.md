# Testing Strategy

This project uses a **two-tier testing approach**: **Unit Tests** and **Feature Tests**.

## Testing Philosophy

### 🎯 **Unit Tests** (Fast, Isolated)
- Test individual **services** and **repositories** in isolation
- **Mock dependencies** - Don't touch the database
- Fast execution
- Focus on **business logic** correctness

### 🌐 **Feature Tests** (Integration, Real Database)
- Test **complete API endpoints**
- Use **real database** (SQLite in-memory for tests)
- Test **full request/response cycle**
- Validate **integration** between layers

---

## Test Structure

```
tests/
├── Unit/
│   └── Services/
│       ├── ProductServiceTest.php     # Unit tests for ProductService
│       └── CategoryServiceTest.php    # Unit tests for CategoryService
│
└── Feature/
    ├── ProductsApiTest.php            # Full API tests for Products
    └── CategoriesApiTest.php          # Full API tests for Categories
```

---

## Running Tests

### Run all tests
```bash
php artisan test
```

### Run only unit tests
```bash
php artisan test --testsuite=Unit
```

### Run only feature tests
```bash
php artisan test --testsuite=Feature
```

### Run specific test file
```bash
php artisan test tests/Unit/Services/ProductServiceTest.php
```

### Run specific test
```bash
php artisan test --filter "can create a product"
```

### Run with coverage
```bash
php artisan test --coverage
```

---

## Unit Tests (Service Layer)

### Purpose
Test **business logic** in services **without** database dependencies.

### Approach
1. **Mock the Repository** interface
2. Test service methods in isolation
3. Verify service calls repository correctly
4. No database interactions

### Example Structure

```php
describe('ProductService', function () {
    
    beforeEach(function () {
        // Mock the repository
        $this->mockRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->productService = new ProductService($this->mockRepository);
    });
    
    it('can create a product', function () {
        // Arrange: Set up mocks
        $productData = ['name' => 'Test'];
        $mockProduct = Products::factory()->make();
        
        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->with($productData)
            ->andReturn($mockProduct);
        
        // Act: Call service method
        $result = $this->productService->create($productData);
        
        // Assert: Verify result
        expect($result)->toBe($mockProduct);
    });
});
```

### What Unit Tests Cover
✅ Service method logic  
✅ Repository interaction  
✅ Business rules  
✅ Data transformation  
✅ Exception handling  

### What Unit Tests DON'T Cover
❌ Database operations (handled by Repository)  
❌ HTTP requests/responses (handled by Feature tests)  
❌ Full integration flow  

---

## Feature Tests (API Layer)

### Purpose
Test **complete API endpoints** with **real database**.

### Approach
1. Use **RefreshDatabase** trait (resets DB between tests)
2. Create **real data** using factories
3. Make **HTTP requests** to API endpoints
4. Verify **JSON responses** and **database state**

### Example Structure

```php
describe('Products API', function () {
    
    uses(RefreshDatabase::class);  // Resets database before each test
    
    it('can create a product', function () {
        // Arrange: Prepare request data
        $productData = [
            'name' => 'New Product',
            'description' => 'Description',
            'active' => true
        ];
        
        // Act: Make API request
        $response = $this->postJson('/api/products', $productData);
        
        // Assert: Verify response
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'New Product']
            ]);
        
        // Assert: Verify database
        $this->assertDatabaseHas('products', [
            'name' => 'New Product'
        ]);
    });
});
```

### What Feature Tests Cover
✅ Complete API endpoint functionality  
✅ Request validation  
✅ Response format and status codes  
✅ Database persistence  
✅ Relationships (products ↔ categories)  
✅ Error handling  
✅ Integration between all layers  

### What Feature Tests DON'T Cover
❌ Internal service logic details  
❌ Repository implementation details  

---

## When to Use Each Test Type

### Use **Unit Tests** when:
- ✅ Testing complex business logic
- ✅ Testing service methods independently
- ✅ Testing edge cases and error scenarios
- ✅ You need **fast** test execution
- ✅ Testing without database setup

### Use **Feature Tests** when:
- ✅ Testing complete API functionality
- ✅ Testing request/response flow
- ✅ Testing database interactions
- ✅ Testing relationships between models
- ✅ Ensuring API contracts are met

---

## Test Coverage

### Recommended Coverage

| Layer | Test Type | Coverage Target |
|-------|-----------|----------------|
| Services | Unit Tests | 80-90% |
| API Endpoints | Feature Tests | 100% |
| Repositories | Feature Tests | (Tested indirectly) |
| Controllers | Feature Tests | (Tested indirectly) |

---

## Best Practices

### 1. **AAA Pattern** (Arrange-Act-Assert)
```php
it('can create a product', function () {
    // Arrange: Set up test data and mocks
    $data = ['name' => 'Product'];
    
    // Act: Execute the code being tested
    $result = $service->create($data);
    
    // Assert: Verify the outcome
    expect($result->name)->toBe('Product');
});
```

### 2. **One Assertion Per Test** (when possible)
Each test should verify **one thing**.

### 3. **Descriptive Test Names**
```php
// ✅ Good
it('returns 404 when product does not exist')

// ❌ Bad
it('test product')
```

### 4. **Use Factories**
```php
// ✅ Good
$product = Products::factory()->create();

// ❌ Bad
$product = Products::create(['name' => 'Test', ...]);
```

### 5. **Test Edge Cases**
- Empty data
- Invalid data
- Missing relationships
- Boundary conditions (max length, etc.)

---

## Example Test Scenarios

### ProductService Unit Tests
- ✅ Can get all products
- ✅ Can get active products only
- ✅ Can get product by ID
- ✅ Can create product without categories
- ✅ Can create product with categories
- ✅ Can update product
- ✅ Can delete product
- ✅ Can attach/detach/sync categories

### Products API Feature Tests
- ✅ Can list all products
- ✅ Can list with pagination
- ✅ Can filter active products
- ✅ Can create product
- ✅ Validates required fields
- ✅ Validates field lengths
- ✅ Can show single product
- ✅ Returns 404 for non-existent product
- ✅ Can update product
- ✅ Can update product categories
- ✅ Can delete product
- ✅ Handles relationships correctly

---

## Running Tests in CI/CD

### GitHub Actions Example
```yaml
test:
  runs-on: ubuntu-latest
  steps:
    - uses: actions/checkout@v2
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
    - name: Install Dependencies
      run: composer install
    - name: Run Tests
      run: php artisan test
```

---

## Common Issues & Solutions

### Issue: Factory not found
**Solution**: Make sure model has `HasFactory` trait and factory exists

### Issue: Database connection errors
**Solution**: Check `phpunit.xml` has correct database config (SQLite in-memory)

### Issue: Mock expectations not met
**Solution**: Verify mock setup matches actual method calls

---

## Next Steps

1. ✅ Run tests: `php artisan test`
2. ✅ Check coverage: `php artisan test --coverage`
3. ✅ Fix any failing tests
4. ✅ Add more tests as you add features
5. ✅ Maintain >80% code coverage

