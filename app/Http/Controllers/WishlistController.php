<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\WishlistPostRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $wishlistProducts = $user->wishlistProducts;

        return response()->json([
            'success' => true,
            'data' => $wishlistProducts
        ]);
    }

    public function store(WishlistPostRequest $request): JsonResponse
    {
        $user = Auth::user();
        $productId = $request->validated()['product_id'];

        $user->wishlistProducts()->attach($productId);

        $product = Product::find($productId);

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist successfully',
            'data' => $product
        ], 201);
    }

    public function destroy(Product $product): JsonResponse
    {
        $user = Auth::user();

        if (!$user->wishlistProducts()->where('product_id', $product->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Product is not in your wishlist'
            ], 404);
        }

        $user->wishlistProducts()->detach($product->id);

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist successfully'
        ]);
    }
}