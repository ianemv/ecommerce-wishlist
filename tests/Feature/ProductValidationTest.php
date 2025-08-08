<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function getAdminToken(): string
    {
        $admin = User::factory()->create(['role' => 'admin']);
        return $admin->createToken('test-token')->plainTextToken;
    }

    public function test_product_creation_requires_name()
    {
        $token = $this->getAdminToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/products', [
            'price' => 99.99,
            'description' => 'Test description'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed.',
                     'errors' => [
                         'name' => ['Product name is required.']
                     ]
                 ]);
    }

    public function test_product_creation_requires_price()
    {
        $token = $this->getAdminToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/products', [
            'name' => 'Test Product',
            'description' => 'Test description'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['price'])
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed.',
                     'errors' => [
                         'price' => ['Product price is required.']
                     ]
                 ]);
    }

    public function test_product_creation_price_must_be_numeric()
    {
        $token = $this->getAdminToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 'not-a-number',
            'description' => 'Test description'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['price'])
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed.',
                     'errors' => [
                         'price' => ['Product price must be a number.']
                     ]
                 ]);
    }

    public function test_product_creation_price_cannot_be_negative()
    {
        $token = $this->getAdminToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => -10.50,
            'description' => 'Test description'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['price'])
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed.'
                 ]);
    }

    public function test_product_creation_name_cannot_exceed_255_characters()
    {
        $token = $this->getAdminToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/products', [
            'name' => str_repeat('a', 256),
            'price' => 99.99,
            'description' => 'Test description'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed.',
                     'errors' => [
                         'name' => ['Product name cannot exceed 255 characters.']
                     ]
                 ]);
    }

    public function test_product_creation_description_cannot_exceed_1000_characters()
    {
        $token = $this->getAdminToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'description' => str_repeat('a', 1001)
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['description'])
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed.',
                     'errors' => [
                         'description' => ['Product description cannot exceed 1000 characters.']
                     ]
                 ]);
    }

    public function test_product_update_validation_works_with_sometimes_rules()
    {
        $token = $this->getAdminToken();
        $product = Product::factory()->create();

        // Update only name (should work)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(200);

        // Update with invalid price (should fail)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/products/{$product->id}", [
            'price' => -50
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['price']);
    }

    public function test_valid_product_creation_passes_validation()
    {
        $token = $this->getAdminToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/products', [
            'name' => 'Valid Product',
            'price' => 149.99,
            'description' => 'Valid description'
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Product created successfully'
                 ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Valid Product',
            'price' => '149.99'
        ]);
    }
}