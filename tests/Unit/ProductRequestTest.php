<?php

namespace Tests\Unit;

use App\Http\Requests\ProductRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProductRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorize_returns_true()
    {
        $request = new ProductRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_valid_data_passes_validation()
    {
        $request = new ProductRequest();
        $validator = Validator::make([
            'name' => 'Test Product',
            'price' => 99.99,
            'description' => 'Test description'
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_missing_name_fails_validation()
    {
        $request = new ProductRequest();
        $validator = Validator::make([
            'price' => 99.99,
            'description' => 'Test description'
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function test_missing_price_fails_validation()
    {
        $request = new ProductRequest();
        $validator = Validator::make([
            'name' => 'Test Product',
            'description' => 'Test description'
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('price'));
    }

    public function test_negative_price_fails_validation()
    {
        $request = new ProductRequest();
        $validator = Validator::make([
            'name' => 'Test Product',
            'price' => -10.50,
            'description' => 'Test description'
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('price'));
    }

    public function test_non_numeric_price_fails_validation()
    {
        $request = new ProductRequest();
        $validator = Validator::make([
            'name' => 'Test Product',
            'price' => 'not-a-number',
            'description' => 'Test description'
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('price'));
    }

    public function test_long_name_fails_validation()
    {
        $request = new ProductRequest();
        $validator = Validator::make([
            'name' => str_repeat('a', 256),
            'price' => 99.99,
            'description' => 'Test description'
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function test_long_description_fails_validation()
    {
        $request = new ProductRequest();
        $validator = Validator::make([
            'name' => 'Test Product',
            'price' => 99.99,
            'description' => str_repeat('a', 1001)
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('description'));
    }

    public function test_description_is_optional()
    {
        $request = new ProductRequest();
        $validator = Validator::make([
            'name' => 'Test Product',
            'price' => 99.99
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_custom_validation_messages()
    {
        $request = new ProductRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('price.required', $messages);
        $this->assertArrayHasKey('price.numeric', $messages);
        $this->assertEquals('Product name is required.', $messages['name.required']);
        $this->assertEquals('Product price is required.', $messages['price.required']);
    }

    public function test_update_rules_use_sometimes()
    {
        // Simulate PUT request
        $request = new ProductRequest();
        $request->setMethod('PUT');
        
        $rules = $request->rules();
        
        // For update requests, name and price should use 'sometimes'
        $this->assertContains('sometimes', $rules['name']);
        $this->assertContains('sometimes', $rules['price']);
    }
}