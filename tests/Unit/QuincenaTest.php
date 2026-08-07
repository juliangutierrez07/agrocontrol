<?php

use PHPUnit\Framework\TestCase;

/**
 * Cubre indice_quincena() de Config/helpers.php (extraída de
 * Pages/historial_quincenas_leche.php): a qué periodo de 15 dias, contando
 * desde una fecha de inicio, pertenece una fecha dada.
 */
final class QuincenaTest extends TestCase
{
    /** @dataProvider fechasDePrimeraQuincena */
    public function testFechasDentroDeLaPrimeraQuincenaDevuelvenIndiceCero(string $fecha): void
    {
        $inicio = new DateTime('2026-01-01');
        $this->assertSame(0, indice_quincena($inicio, new DateTime($fecha)));
    }

    public static function fechasDePrimeraQuincena(): array
    {
        return [
            'la propia fecha de inicio' => ['2026-01-01'],
            'ultimo dia del primer periodo (dia 14)' => ['2026-01-15'],
        ];
    }

    public function testElPrimerDiaDeLaSegundaQuincenaEsIndiceUno(): void
    {
        // Dia 15 desde el inicio ya cae en el segundo periodo.
        $inicio = new DateTime('2026-01-01');
        $this->assertSame(1, indice_quincena($inicio, new DateTime('2026-01-16')));
    }

    public function testCalculaElIndiceCorrectoVariosMesesDespues(): void
    {
        // 75 dias desde el 1 de enero -> floor(75/15) = indice 5.
        $inicio = new DateTime('2026-01-01');
        $this->assertSame(5, indice_quincena($inicio, new DateTime('2026-03-17')));
    }
}
