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
        $user = $request->user();

        $cart = Cart::with(['items.product', 'items.event'])
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
                    'item' => $item->item_type === 'product'
                        ? $item->product
                        : $item->event,
                    'type' => $item->item_type
                ];
            })
        ]);
    }


    public function updateItem(Request $request, CartItem $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        // El item debe pertenecer al carrito del usuario autenticado; si no,
        // cualquiera podria modificar la cantidad de items del carrito de otro
        // usuario con solo adivinar/incrementar el id.
        if ($item->cart->user_id != $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $item->update(['quantity' => $request->quantity]);
        return response()->json(['message' => 'Item updated']);
    }

    public function removeItem(Request $request, CartItem $item)
    {
        if ($item->cart->user_id != $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $item->delete();
        return response()->json(['message' => 'Item removed']);
    }


    public function addItem(Request $request)
    {
        $request->validate([
            'item_type' => 'required|in:product,event',
            'item_id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1'
        ]);


        $user = $request->user();

        $cart = Cart::firstOrCreate([
            'user_id' => $user->id,
            'status' => 'active'
        ]);

        $existingItem = $cart->items()
            ->where('item_type', $request->item_type)
            ->where('item_id', $request->item_id)
            ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + ($request->quantity ?? 1)
            ]);
        } else {
            $cart->items()->create([
                'item_type' => $request->item_type,
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


    public function clearCart(Request $request)
    {
        $user = $request->user();

        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $cart->items()->delete();

        return response()->json(['message' => 'Cart cleared successfully']);
    }
}
