<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationProduct;
use App\Models\Mutation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);

        $locations = Location::paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $locations
        ]);
    }
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:locations,code',
            'address' => 'required|string|max:255',
            'status' => 'sometimes|boolean',
        ]);

        $location = Location::create($validated);

        return response()->json([
            'success' => true,
            'data' => $location
        ], 201);
    }
    public function show(Location $location): JsonResponse
    {
        $location->load('products');

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $location
        ]);
    }
    public function update(Request $request, Location $location): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:locations,code,' . $location->id,
            'address' => 'required|string|max:255',
            'status' => 'sometimes|boolean',
        ]);

        $location->update($validated);

        return response()->json([
            'success' => true,
            'data' => $location
        ]);
    }
    public function destroy(Location $location): JsonResponse
    {
        $location->delete();

        return response()->json([
            'success' => true,
            'message' => 'Location deleted successfully'
        ]);
    }

    public function addProduct(Request $request, Location $location): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'stock' => 'required|integer|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        $productId = $validated['product_id'];
        $stock = $validated['stock'];


        $existingPivot = $location->products()->where('product_id', $productId)->first();
        $oldQuantity = $existingPivot ? $existingPivot->pivot->stock : 0;

        if ($existingPivot) {
            $currentStock = $location->products()
                ->where('product_id', $productId)
                ->first()
                ->pivot
                ->stock ?? 0;

            $newStock = $currentStock + $stock;

            $location->products()->updateExistingPivot($productId, [
                'stock' => $newStock
            ]);
        } else {
            $location->products()->attach($productId, ['stock' => $stock]);
        }
        $locationProductId = LocationProduct::where('product_id', $productId)
            ->where('location_id', $location->id)
            ->value('id');

        Mutation::create([
            'date' => now(),
            'mutation_type' => 'in',
            'quantity' => $stock,
            'old_quantity' => $oldQuantity,
            'note' => $validated['note'] ?? 'Increase/Decrease product at location',
            'user_id' => $request->user()->id,
            'location_product_id' => $locationProductId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added/updated successfully at the location',
            'data' => $location->load('products')
        ]);
    }

    public function outProduct(Request $request, Location $location): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255',
            'product_id' => 'required|exists:products,id',
        ]);

        $quantityOut = $validated['quantity'];

        $locationProduct = LocationProduct::where('product_id', $request->product_id)
            ->where('location_id', $location->id)
            ->first();

        if (!$locationProduct) {
            return response()->json([
                'success' => false,
                'message' => 'Product is not available at this location.'
            ], 404);
        }

        $oldQuantity = $locationProduct->stock;

        if ($quantityOut > $oldQuantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock for this product at the location.',
                'available_stock' => $oldQuantity
            ], 400);
        }

        $locationProduct->stock -= $quantityOut;
        $locationProduct->save();

        Mutation::create([
            'date' => now(),
            'mutation_type' => 'out',
            'quantity' => $quantityOut,
            'old_quantity' => $oldQuantity,
            'note' => $validated['note'] ?? 'Product stock out',
            'user_id' => $request->user()->id,
            'location_product_id' => $locationProduct->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product stock has been deducted successfully at the location.',
            'data' => $location->load('products')
        ]);
    }
}
