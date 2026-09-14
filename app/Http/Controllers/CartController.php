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
            'quantity' => 'required|integer|min:1|max:' . self::MAX_CANTIDAD,
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


    /** Tope por linea del carrito. */
    private const MAX_CANTIDAD = 99;

    public function addItem(Request $request)
    {
        $datos = $request->validate([
            'item_type' => 'required|in:product,event',
            'item_id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1|max:' . self::MAX_CANTIDAD,
        ]);

        // El articulo tiene que EXISTIR. Antes 'item_id' solo se validaba como
        // entero, asi que se podian meter en el carrito referencias a productos
        // o eventos inexistentes: getCart() los devolvia como null y la vista
        // del carrito quedaba con huecos.
        //
        // La tabla depende del tipo, asi que no sirve una regla exists: fija.
        $modelo = $datos['item_type'] === 'product' ? \App\Models\Product::class : \App\Models\Calendar::class;
        if (! $modelo::whereKey($datos['item_id'])->exists()) {
            return response()->json(['message' => 'El artículo no existe'], 422);
        }

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
            // La SUMA tambien se acota: la validacion de arriba solo mira la
            // cantidad de ESTA peticion, asi que sin este min() se podia llegar
            // a cualquier numero repitiendo la llamada.
            $existingItem->update([
                'quantity' => min(
                    $existingItem->quantity + ($datos['quantity'] ?? 1),
                    self::MAX_CANTIDAD
                ),
            ]);
        } else {
            $cart->items()->create([
                'item_type' => $datos['item_type'],
                'item_id' => $datos['item_id'],
                'quantity' => $datos['quantity'] ?? 1,
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
