<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function getCart(Request $request)
    {

        $user = User::findOrFail($request['user_id']);

        if (!$user) {
            return response()->json(['items' => []]);
        }

        $cart = Cart::with(['items.product'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            return response()->json(['items' => []]);
        }

        return response()->json([
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'product' => $item->product
                ];
            })
        ]);
    }



    public function updateItem(Request $request, CartItem $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $item->update(['quantity' => $request->quantity]);
        return response()->json(['message' => 'Item updated']);
    }

    public function removeItem(CartItem $item)
    {
        $item->delete();
        return response()->json(['message' => 'Item removed']);
    }


    public function addItem(Request $request)
    {
        $request->validate([
            'item_type' => 'required|in:product',
            'item_id' => 'required|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        
        $user = User::findOrFail($request['user_id']);

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $cart = Cart::firstOrCreate([
            'user_id' => $user->id,
            'status' => 'active'
        ]);

        $existingItem = $cart->items()
            ->where('item_type', 'product')
            ->where('item_id', $request->item_id)
            ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + ($request->quantity ?? 1)
            ]);
        } else {
            $cart->items()->create([
                'item_type' => 'product',
                'item_id' => $request->item_id,
                'quantity' => $request->quantity ?? 1
            ]);
        }

        // Cargar los productos nuevamente
        $cart->load('items.product');

        return response()->json([
            'message' => 'Item added to cart',
            'cart' => $cart
        ]);
    }
}