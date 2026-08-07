<?php

use PHPUnit\Framework\TestCase;

/**
 * Cubre calcular_caida_produccion() de Config/helpers.php (extraída de
 * Pages/crearL.php y Pages/ActualizarL.php). El umbral de alerta (>= 8) vive
 * en cada controlador, no aquí — estos tests verifican el porcentaje que la
 * función calcula, incluyendo el límite exacto que dispara la alerta.
 */
final class ProduccionAlertaTest extends TestCase
{
    public function testCalculaElPorcentajeDeCaidaCuandoLaProduccionBaja(): void
    {
        // 20L -> 15L es una caida del 25%, dispara alerta (>= 8).
        $this->assertSame(25.0, calcular_caida_produccion(20.0, 15.0));
    }

    public function testDevuelveCeroONegativoCuandoNoHayCaida(): void
    {
        // Produccion estable: no hay caida.
        $this->assertSame(0.0, calcular_caida_produccion(20.0, 20.0));
        // Produccion sube: valor negativo, tampoco dispara alerta (< 8).
        $this->assertSame(-25.0, calcular_caida_produccion(20.0, 25.0));
        // Sin produccion previa no debe intentar dividir entre cero.
        $this->assertSame(0.0, calcular_caida_produccion(0.0, 10.0));
    }

    public function testElLimiteExactoDelUmbralDeAlertaEsOchoPorciento(): void
    {
        // 100L -> 92L es una caida de exactamente 8.0%, el limite que usan
        // crearL.php y ActualizarL.php para disparar la alerta (>= 8).
        $caida = calcular_caida_produccion(100.0, 92.0);
        $this->assertSame(8.0, $caida);
        $this->assertTrue($caida >= 8);

        // Un decimal por debajo del limite no debe disparar la alerta.
        $caidaLeve = calcular_caida_produccion(100.0, 92.1);
        $this->assertLessThan(8.0, $caidaLeve);
    }
}
