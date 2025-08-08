<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_different_users_can_add_same_product()
    {
        // Create a product
        $product = Product::factory()->create(['name' => 'Test Product']);

        // Create first user and authenticate
        $user1 = User::factory()->create(['email' => 'user1@test.com', 'role' => 'user']);
        $this->actingAs($user1, 'sanctum');

        // Add product to user1's wishlist
        $response1 = $this->postJson('/api/wishlist', [
            'product_id' => $product->id
        ]);

        $response1->assertStatus(201);

        // Verify user1 has the product in wishlist
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user1->id,
            'product_id' => $product->id
        ]);

        // Create second user and authenticate as them
        $user2 = User::factory()->create(['email' => 'user2@test.com', 'role' => 'user']);
        $this->actingAs($user2, 'sanctum');

        // User2 should be able to add the same product
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

        // Verify we have exactly 2 wishlist entries
        $this->assertDatabaseCount('wishlists', 2);
    }

    public function test_same_user_cannot_add_same_product_twice()
    {
        // Create a product
        $product = Product::factory()->create(['name' => 'Test Product']);

        // Create user and authenticate
        $user = User::factory()->create(['email' => 'user@test.com', 'role' => 'user']);
        $this->actingAs($user, 'sanctum');

        // Add product to wishlist first time
        $response1 = $this->postJson('/api/wishlist', [
            'product_id' => $product->id
        ]);

        $response1->assertStatus(201);

        // Try to add same product again - should fail
        $response2 = $this->postJson('/api/wishlist', [
            'product_id' => $product->id
        ]);

        $response2->assertStatus(422)
                  ->assertJson([
                      'success' => false,
                      'message' => 'Validation failed.',
                      'errors' => [
                          'product_id' => ['This product is already in your wishlist.']
                      ]
                  ]);

        // Should still have only 1 wishlist entry
        $this->assertDatabaseCount('wishlists', 1);
    }
}