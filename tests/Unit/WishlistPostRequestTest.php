<?php

namespace Tests\Unit;

use App\Http\Requests\WishlistPostRequest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class WishlistPostRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorize_returns_true()
    {
        $request = new WishlistPostRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_basic_validation_rules()
    {
        $request = new WishlistPostRequest();
        $rules = $request->rules();
        
        $this->assertArrayHasKey('product_id', $rules);
        $this->assertIsArray($rules['product_id']);
        $this->assertContains('required', $rules['product_id']);
        $this->assertContains('integer', $rules['product_id']);
        $this->assertContains('exists:products,id', $rules['product_id']);
        
        // Note: Custom rule NotInWishlist requires authentication context
        // so it's tested in feature tests instead
    }

    public function test_missing_product_id_fails_validation()
    {
        $request = new WishlistPostRequest();
        
        // Test only basic rules (excluding custom rule)
        $basicRules = [
            'product_id' => ['required', 'integer', 'exists:products,id']
        ];
        
        $validator = Validator::make([], $basicRules);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('product_id'));
    }

    public function test_non_integer_product_id_fails_validation()
    {
        $request = new WishlistPostRequest();
        
        // Test only basic rules (excluding custom rule)
        $basicRules = [
            'product_id' => ['required', 'integer', 'exists:products,id']
        ];
        
        $validator = Validator::make([
            'product_id' => 'not-an-integer'
        ], $basicRules);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('product_id'));
    }

    public function test_non_existent_product_fails_validation()
    {
        $request = new WishlistPostRequest();
        
        // Test only basic rules (excluding custom rule)
        $basicRules = [
            'product_id' => ['required', 'integer', 'exists:products,id']
        ];
        
        $validator = Validator::make([
            'product_id' => 999999 // Non-existent ID
        ], $basicRules);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('product_id'));
    }

    public function test_custom_validation_messages()
    {
        $request = new WishlistPostRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('product_id.required', $messages);
        $this->assertArrayHasKey('product_id.integer', $messages);
        $this->assertArrayHasKey('product_id.exists', $messages);
        $this->assertEquals('Product ID is required.', $messages['product_id.required']);
        $this->assertEquals('Product ID must be an integer.', $messages['product_id.integer']);
        $this->assertEquals('The selected product does not exist.', $messages['product_id.exists']);
    }

    public function test_rules_array_structure()
    {
        $request = new WishlistPostRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('product_id', $rules);
        $this->assertIsArray($rules['product_id']);
        $this->assertContains('required', $rules['product_id']);
        $this->assertContains('integer', $rules['product_id']);
        $this->assertContains('exists:products,id', $rules['product_id']);
        
        // Check that NotInWishlist rule is included
        $hasNotInWishlistRule = false;
        foreach ($rules['product_id'] as $rule) {
            if ($rule instanceof \App\Rules\NotInWishlist) {
                $hasNotInWishlistRule = true;
                break;
            }
        }
        $this->assertTrue($hasNotInWishlistRule, 'NotInWishlist rule should be included in product_id validation');
    }
}