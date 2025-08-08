<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_has_correct_fillable_attributes()
    {
        $product = new Product();
        
        $this->assertEquals(['name', 'price', 'description'], $product->getFillable());
    }

    public function test_product_price_is_cast_to_decimal()
    {
        $product = Product::factory()->create(['price' => 99.99]);
        
        // Laravel's decimal cast returns a string with proper decimal formatting
        $this->assertEquals('99.99', $product->price);
        $this->assertIsString($product->price);
        
        // Test that decimal places are preserved
        $product2 = Product::factory()->create(['price' => 100]);
        $this->assertEquals('100.00', $product2->price);
    }

    public function test_product_belongs_to_many_users_through_wishlists()
    {
        $product = Product::factory()->create();
        $users = User::factory(3)->create();
        
        foreach ($users as $user) {
            $user->wishlistProducts()->attach($product->id);
        }
        
        $this->assertCount(3, $product->wishlists);
    }
}