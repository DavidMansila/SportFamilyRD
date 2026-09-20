<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integridad del carrito: existencias, cantidades y lineas huerfanas.
 */
class CarritoIntegridadTest extends TestCase
{
    use RefreshDatabase;

    private function producto(int $stock, float $precio = 10.0): Product
    {
        return Product::create([
            'name' => 'Balon',
            'description' => 'Balon de prueba',
            'price' => $precio,
            'stock' => $stock,
            'category' => 'Accesorios',
            'image' => 'https://ejemplo.test/balon.png',
        ]);
    }

    public function test_no_se_puede_añadir_mas_de_lo_que_hay_en_existencias(): void
    {
        $usuario = User::factory()->create();
        $producto = $this->producto(4);

        $this->actingAs($usuario)
            ->postJson('/api/cart/items', [
                'item_type' => 'product',
                'item_id' => $producto->id,
                'quantity' => 10,
            ])
            ->assertStatus(422)
            ->assertJsonPath('disponible', 4);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_sumar_de_uno_en_uno_tampoco_pasa_de_las_existencias(): void
    {
        $usuario = User::factory()->create();
        $producto = $this->producto(2);

        foreach (range(1, 2) as $i) {
            $this->actingAs($usuario)
                ->postJson('/api/cart/items', [
                    'item_type' => 'product',
                    'item_id' => $producto->id,
                    'quantity' => 1,
                ])
                ->assertOk();
        }

        // La tercera ya no cabe.
        $this->actingAs($usuario)
            ->postJson('/api/cart/items', [
                'item_type' => 'product',
                'item_id' => $producto->id,
                'quantity' => 1,
            ])
            ->assertStatus(422);

        $this->assertSame(2, CartItem::firstOrFail()->quantity);
    }

    public function test_cambiar_la_cantidad_tampoco_puede_superar_las_existencias(): void
    {
        $usuario = User::factory()->create();
        $producto = $this->producto(3);

        $this->actingAs($usuario)
            ->postJson('/api/cart/items', [
                'item_type' => 'product',
                'item_id' => $producto->id,
                'quantity' => 1,
            ])
            ->assertOk();

        $linea = CartItem::firstOrFail();

        // Subir la cantidad por la otra puerta era el camino corto para
        // saltarse el control al añadir.
        $this->actingAs($usuario)
            ->putJson("/api/cart/items/{$linea->id}", ['quantity' => 50])
            ->assertStatus(422);

        $this->assertSame(1, $linea->fresh()->quantity);
    }

    public function test_borrar_un_producto_se_lleva_sus_lineas_de_carrito(): void
    {
        $usuario = User::factory()->create();
        $producto = $this->producto(10);

        $this->actingAs($usuario)
            ->postJson('/api/cart/items', [
                'item_type' => 'product',
                'item_id' => $producto->id,
                'quantity' => 2,
            ])
            ->assertOk();

        $this->assertDatabaseCount('cart_items', 1);

        $producto->delete();

        // Antes la linea sobrevivia apuntando a un id inexistente y el carrito
        // la mostraba como "Item no disponible" indefinidamente.
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_la_base_impide_dos_lineas_del_mismo_articulo(): void
    {
        $usuario = User::factory()->create();
        $producto = $this->producto(10);
        $carrito = Cart::create(['user_id' => $usuario->id, 'status' => 'active']);

        CartItem::create([
            'cart_id' => $carrito->id,
            'item_type' => 'product',
            'item_id' => $producto->id,
            'quantity' => 1,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        CartItem::create([
            'cart_id' => $carrito->id,
            'item_type' => 'product',
            'item_id' => $producto->id,
            'quantity' => 1,
        ]);
    }
}
