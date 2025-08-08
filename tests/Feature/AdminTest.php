<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $productData = [
            'name' => 'Admin Product',
            'price' => 199.99,
            'description' => 'Product created by admin'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/products', $productData);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Product created successfully'
                 ]);

        $this->assertDatabaseHas('products', $productData);
    }

    public function test_regular_user_cannot_create_product()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test-token')->plainTextToken;

        $productData = [
            'name' => 'User Product',
            'price' => 99.99,
            'description' => 'Product attempt by user'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/products', $productData);

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Access denied. Admin privileges required.'
                 ]);

        $this->assertDatabaseMissing('products', $productData);
    }

    public function test_admin_can_update_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;
        $product = Product::factory()->create();

        $updateData = [
            'name' => 'Updated by Admin',
            'price' => 299.99
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Product updated successfully'
                 ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated by Admin',
            'price' => 299.99
        ]);
    }

    public function test_regular_user_cannot_update_product()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test-token')->plainTextToken;
        $product = Product::factory()->create();

        $updateData = [
            'name' => 'User Update Attempt',
            'price' => 199.99
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Access denied. Admin privileges required.'
                 ]);
    }

    public function test_admin_can_delete_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;
        $product = Product::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Product deleted successfully'
                 ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }

    public function test_regular_user_cannot_delete_product()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test-token')->plainTextToken;
        $product = Product::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Access denied. Admin privileges required.'
                 ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id
        ]);
    }

    public function test_both_admin_and_user_can_view_products()
    {
        Product::factory(3)->create();
        
        // Test admin access
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->getJson('/api/products');
        $response->assertStatus(200);

        // Test user access
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->getJson('/api/products');
        $response->assertStatus(200);
    }
}