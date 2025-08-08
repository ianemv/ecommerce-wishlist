<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_wishlist()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $products = Product::factory(3)->create();
        
        $user->wishlistProducts()->attach($products->pluck('id'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/wishlist');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'name', 'price', 'description']
                     ]
                 ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_authenticated_user_can_add_product_to_wishlist()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
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
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);
    }

    public function test_cannot_add_same_product_to_wishlist_twice()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $product = Product::factory()->create();

        $user->wishlistProducts()->attach($product->id);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/wishlist', [
            'product_id' => $product->id
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed.',
                     'errors' => [
                         'product_id' => ['This product is already in your wishlist.']
                     ]
                 ]);
    }

    public function test_authenticated_user_can_remove_product_from_wishlist()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $product = Product::factory()->create();

        $user->wishlistProducts()->attach($product->id);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/wishlist/{$product->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Product removed from wishlist successfully'
                 ]);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);
    }

    public function test_cannot_remove_product_not_in_wishlist()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $product = Product::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/wishlist/{$product->id}");

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Product is not in your wishlist'
                 ]);
    }

    public function test_guest_cannot_access_wishlist()
    {
        $response = $this->getJson('/api/wishlist');
        $response->assertStatus(401);
    }

    public function test_guest_cannot_add_to_wishlist()
    {
        $product = Product::factory()->create();
        
        $response = $this->postJson('/api/wishlist', [
            'product_id' => $product->id
        ]);

        $response->assertStatus(401);
    }
}