<?php

use PHPUnit\Framework\TestCase;

/**
 * Cubre estado_vaca_valido() de Config/helpers.php (extraída de
 * Pages/CrearV.php, Pages/Registro_Vacas.php y Pages/editar.php, donde antes
 * el mismo array de estados permitidos estaba triplicado).
 */
final class EstadoVacaTest extends TestCase
{
    /** @dataProvider estadosValidos */
    public function testAceptaEstadosValidos(string $estado): void
    {
        $this->assertTrue(estado_vaca_valido($estado));
    }

    public static function estadosValidos(): array
    {
        return [
            'produccion' => ['produccion'],
            'secado' => ['secado'],
            'enrazada' => ['enrazada'],
        ];
    }

    /** @dataProvider estadosInvalidos */
    public function testRechazaEstadosInvalidos(string $estado): void
    {
        $this->assertFalse(estado_vaca_valido($estado));
    }

    public static function estadosInvalidos(): array
    {
        return [
            'vacio' => [''],
            'mayusculas' => ['Produccion'],
            'con espacios' => ['produccion '],
            'valor arbitrario' => ['muerta'],
        ];
    }
}
