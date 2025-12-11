<?php

namespace App\Http\Controllers\Admin;

use App\Models\PreferredItem;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PreferredItemController extends Controller
{
    /**
     * Get all preferred items for current user with product details
     */
    public function index()
    {
        $userId = Auth::id();
        
        $preferredItems = PreferredItem::where('user_id', $userId)
            ->with(['product' => function($q) {
                $q->with('purchase');
            }])
            ->get()
            ->map(function($item) {
                $prod = $item->product;
                $purchase = optional($prod->purchase);
                
                return [
                    'id' => $prod->id,
                    'name' => $purchase->product ?? $prod->name ?? 'Unknown',
                    'price_per_packet' => (float) $prod->price,
                    'price_per_sheet' => $purchase && $purchase->packet_size > 0 
                        ? round($prod->price / $purchase->packet_size, 2) 
                        : 0,
                    'packet_size' => (int) ($purchase->packet_size ?? 1),
                    'packet_quantity' => (int) ($purchase->packet_quantity ?? 0),
                    'loose_sheets' => (int) ($purchase->loose_sheets ?? 0),
                    'unit_type' => $prod->unit_type ?? 'packet'
                ];
            });
        
        return response()->json(['success' => true, 'data' => $preferredItems]);
    }

    /**
     * Add item to preferred
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;

        // Check if already favorited
        $exists = PreferredItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();

        if($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Item already in favorites'
            ], 409);
        }

        PreferredItem::create([
            'user_id' => $userId,
            'product_id' => $productId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added to favorites'
        ]);
    }

    /**
     * Remove item from preferred
     */
    public function destroy($productId)
    {
        $userId = Auth::id();

        PreferredItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from favorites'
        ]);
    }
}
