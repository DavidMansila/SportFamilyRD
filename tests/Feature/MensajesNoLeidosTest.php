<?php

namespace Tests\Feature;

use App\Models\Message;
use Illuminate\Database\Query\Expression;
use Tests\TestCase;

/**
 * El filtro de mensajes no leidos no puede enlazar el booleano como parametro.
 *
 * Estas pruebas miran el SQL generado, no el resultado, a proposito: la suite
 * corre sobre SQLite (phpunit.xml) y ahi 0 y 1 SON los booleanos, asi que una
 * prueba funcional pasaria igual con el codigo roto. En produccion la base es
 * Postgres, donde 'messages.read' es boolean de verdad y comparar contra un
 * entero aborta la consulta:
 *
 *   where "read" = 0   -> operator does not exist: boolean = integer
 *
 * Eso devolvia un 500 en GET /api/chats -la bandeja entera- en cuanto habia al
 * menos una conversacion, que es justo por lo que tardo en aparecer: con la
 * tabla de chats vacia, el contador de no leidos nunca llegaba a ejecutarse.
 */
class MensajesNoLeidosTest extends TestCase
{
    public function test_el_filtro_de_no_leidos_usa_el_literal_booleano(): void
    {
        $consulta = Message::query()->noLeidos()->toBase();

        $this->assertStringContainsString('= false', $consulta->toSql());

        // Si el false viajara como parametro, aqui habria un 0.
        $this->assertSame([], $consulta->getBindings());
    }

    public function test_marcar_como_leido_tampoco_enlaza_el_booleano(): void
    {
        $this->assertInstanceOf(Expression::class, Message::booleano(true));
        $this->assertSame('true', (string) Message::booleano(true)->getValue(
            \Illuminate\Support\Facades\DB::connection()->getQueryGrammar()
        ));
    }
}
