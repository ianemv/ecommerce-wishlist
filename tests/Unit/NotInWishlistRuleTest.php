<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use App\Rules\NotInWishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class NotInWishlistRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_passes_when_product_not_in_wishlist()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        Auth::login($user);
        
        $rule = new NotInWishlist();
        $this->assertTrue($rule->passes('product_id', $product->id));
    }

    public function test_fails_when_product_already_in_wishlist()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        // Add product to user's wishlist
        $user->wishlistProducts()->attach($product->id);
        
        Auth::login($user);
        
        $rule = new NotInWishlist();
        $this->assertFalse($rule->passes('product_id', $product->id));
    }

    public function test_fails_when_no_authenticated_user()
    {
        $product = Product::factory()->create();
        
        // Ensure no user is authenticated
        Auth::logout();
        
        $rule = new NotInWishlist();
        $this->assertFalse($rule->passes('product_id', $product->id));
    }

    public function test_returns_correct_error_message()
    {
        $rule = new NotInWishlist();
        $this->assertEquals('This product is already in your wishlist.', $rule->message());
    }

    public function test_passes_for_different_users_with_same_product()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();
        
        // Add product to user1's wishlist
        $user1->wishlistProducts()->attach($product->id);
        
        // Test that user2 can still add the same product
        Auth::login($user2);
        
        $rule = new NotInWishlist();
        $this->assertTrue($rule->passes('product_id', $product->id));
    }
}