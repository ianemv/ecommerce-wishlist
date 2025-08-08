<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_wishlist_products()
    {
        $user = User::factory()->create();
        $products = Product::factory(3)->create();
        
        $user->wishlistProducts()->attach($products->pluck('id'));
        
        $this->assertCount(3, $user->wishlistProducts);
        $this->assertInstanceOf(Product::class, $user->wishlistProducts->first());
    }

    public function test_user_has_api_tokens_trait()
    {
        $user = User::factory()->create();
        
        $this->assertTrue(method_exists($user, 'createToken'));
        $this->assertTrue(method_exists($user, 'tokens'));
    }

    public function test_user_role_functionality()
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $regularUser = User::factory()->create(['role' => 'user']);

        $this->assertTrue($adminUser->isAdmin());
        $this->assertFalse($adminUser->isUser());
        $this->assertTrue($adminUser->hasRole('admin'));
        $this->assertFalse($adminUser->hasRole('user'));

        $this->assertFalse($regularUser->isAdmin());
        $this->assertTrue($regularUser->isUser());
        $this->assertTrue($regularUser->hasRole('user'));
        $this->assertFalse($regularUser->hasRole('admin'));
    }

    public function test_user_default_role_is_user()
    {
        $user = User::factory()->create();
        
        $this->assertEquals('user', $user->role);
        $this->assertTrue($user->isUser());
        $this->assertFalse($user->isAdmin());
    }
}