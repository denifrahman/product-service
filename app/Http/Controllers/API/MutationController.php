<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MutationController extends Controller
{
    
    public function historyByProduct(Request $request, Product $product): JsonResponse
    {
        $mutations = Mutation::with([
            'locationProduct.product',
            'locationProduct.location',
            'user'
        ])
            ->whereHas('locationProduct', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->orderByDesc('date')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $mutations
        ]);
    }

    public function historyByUser(Request $request, User $user): JsonResponse
    {
        $mutations = Mutation::with([
            'locationProduct.product',
            'locationProduct.location',
            'user'
        ])
            ->where('user_id', $user->id)
            ->orderByDesc('date')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $mutations
        ]);
    }
}
