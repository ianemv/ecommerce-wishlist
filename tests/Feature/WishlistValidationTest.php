<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function getUserToken(): string
    {
        $user = User::factory()->create(['role' => 'user']);
        return $user->createToken('test-token')->plainTextToken;
    }

    public function test_wishlist_creation_requires_product_id()
    {
        $token = $this->getUserToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/wishlist', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['product_id'])
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed.',
                     'errors' => [
                         'product_id' => ['Product ID is required.']
                     ]
                 ]);
    }

    public function test_wishlist_creation_product_id_must_be_integer()
    {
        $token = $this->getUserToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/wishlist', [
            'product_id' => 'not-an-integer'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['product_id'])
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed.',
                     'errors' => [
                         'product_id' => ['Product ID must be an integer.']
                     ]
                 ]);
    }

    public function test_wishlist_creation_product_must_exist()
    {
        $token = $this->getUserToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/wishlist', [
            'product_id' => 999999 // Non-existent product
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['product_id'])
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed.',
                     'errors' => [
                         'product_id' => ['The selected product does not exist.']
                     ]
                 ]);
    }

    public function test_wishlist_creation_prevents_duplicate_products()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test-token')->plainTextToken;
        $product = Product::factory()->create();

        // Add product to wishlist first time (should succeed)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/wishlist', [
            'product_id' => $product->id
        ]);

        $response->assertStatus(201);

        // Try to add same product again (should fail)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/wishlist', [
            'product_id' => $product->id
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['product_id'])
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed.',
                     'errors' => [
                         'product_id' => ['This product is already in your wishlist.']
                     ]
                 ]);
    }

    public function test_valid_wishlist_creation_passes_validation()
    {
        $token = $this->getUserToken();
        $product = Product::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/wishlist', [
            'product_id' => $product->id
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Product added to wishlist successfully'
                 ]);

        $this->assertDatabaseHas('wishlists', [
            'product_id' => $product->id
        ]);
    }

    public function test_wishlist_validation_works_for_different_users()
    {
        $product = Product::factory()->create();
        
        // Create User 1 and add product to their wishlist
        $user1 = User::factory()->create(['role' => 'user']);
        $this->actingAs($user1, 'sanctum');

        $response1 = $this->postJson('/api/wishlist', [
            'product_id' => $product->id
        ]);

        $response1->assertStatus(201);
        
        // Verify user 1 has the product in wishlist
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user1->id,
            'product_id' => $product->id
        ]);

        // Create User 2 (different user) - should be able to add same product
        $user2 = User::factory()->create(['role' => 'user']);
        $this->actingAs($user2, 'sanctum');

        $response2 = $this->postJson('/api/wishlist', [
            'product_id' => $product->id
        ]);

        $response2->assertStatus(201)
                  ->assertJson([
                      'success' => true,
                      'message' => 'Product added to wishlist successfully'
                  ]);

        // Verify both users have the product in their separate wishlists
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user1->id,
            'product_id' => $product->id
        ]);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user2->id,
            'product_id' => $product->id
        ]);
        
        // Verify we have exactly 2 wishlist entries for this product
        $this->assertDatabaseCount('wishlists', 2);
    }
}