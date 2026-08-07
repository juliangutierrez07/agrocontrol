<?php

use PHPUnit\Framework\TestCase;

/**
 * Cubre los validadores de entrada de Config/helpers.php: input_string(),
 * input_email(), input_int(), input_float(), input_date(). Son funciones
 * puras (reciben un array + una clave, devuelven o lanzan), sin BD ni sesión.
 */
final class InputValidatorsTest extends TestCase
{
    public function testInputStringAceptaValoresValidos(): void
    {
        $this->assertSame('vaca 123', input_string(['nombre' => '  vaca 123  '], 'nombre', 100));

        // Limite exacto de longitud: debe aceptarse, no lanzar.
        $valorAlLimite = str_repeat('a', 100);
        $this->assertSame($valorAlLimite, input_string(['nombre' => $valorAlLimite], 'nombre', 100));
    }

    /** @dataProvider stringsInvalidos */
    public function testInputStringRechazaValoresInvalidos(array $source, int $max, bool $required): void
    {
        $this->expectException(InvalidArgumentException::class);
        input_string($source, 'nombre', $max, $required);
    }

    public static function stringsInvalidos(): array
    {
        return [
            'obligatorio y vacio' => [['nombre' => '   '], 100, true],
            'supera la longitud maxima' => [['nombre' => str_repeat('a', 101)], 100, true],
        ];
    }

    /** @dataProvider correosValidos */
    public function testInputEmailAceptaCorreosValidos(string $correo): void
    {
        $this->assertSame($correo, input_email(['correo' => $correo], 'correo'));
    }

    public static function correosValidos(): array
    {
        return [
            'simple' => ['usuario@ejemplo.com'],
            'con subdominio' => ['admin@finca.agrocontrol.com'],
            'con signo mas' => ['user+tag@ejemplo.co'],
        ];
    }

    /** @dataProvider correosInvalidos */
    public function testInputEmailRechazaCorreosInvalidos(string $correo): void
    {
        $this->expectException(InvalidArgumentException::class);
        input_email(['correo' => $correo], 'correo');
    }

    public static function correosInvalidos(): array
    {
        return [
            'sin arroba' => ['usuario-ejemplo.com'],
            'sin dominio' => ['usuario@'],
            'con espacios' => ['usuario @ejemplo.com'],
            'vacio' => [''],
        ];
    }

    /** @dataProvider enterosValidos */
    public function testInputIntAceptaValoresValidos(string $valor, int $esperado): void
    {
        $this->assertSame($esperado, input_int(['edad' => $valor], 'edad', 0, 40));
    }

    public static function enterosValidos(): array
    {
        return [
            'limite inferior' => ['0', 0],
            'limite superior' => ['40', 40],
            'valor intermedio' => ['15', 15],
        ];
    }

    /** @dataProvider enterosInvalidos */
    public function testInputIntRechazaValoresInvalidos($valor): void
    {
        $this->expectException(InvalidArgumentException::class);
        input_int(['edad' => $valor], 'edad', 0, 40);
    }

    public static function enterosInvalidos(): array
    {
        return [
            'menor al minimo' => [-1],
            'mayor al maximo' => [41],
            'no numerico' => ['abc'],
        ];
    }

    public function testInputIntLanzaExcepcionCuandoEsObligatorioYFalta(): void
    {
        $this->expectException(InvalidArgumentException::class);
        input_int([], 'edad', 0, 40, true);
    }

    /** @dataProvider flotantesValidos */
    public function testInputFloatAceptaValoresValidos(string $valor, float $esperado): void
    {
        $this->assertSame($esperado, input_float(['litros' => $valor], 'litros', 0, 1000));
    }

    public static function flotantesValidos(): array
    {
        return [
            'limite inferior' => ['0', 0.0],
            'limite superior' => ['1000', 1000.0],
            'decimal intermedio' => ['12.5', 12.5],
        ];
    }

    /** @dataProvider flotantesInvalidos */
    public function testInputFloatRechazaValoresInvalidos($valor): void
    {
        $this->expectException(InvalidArgumentException::class);
        input_float(['litros' => $valor], 'litros', 0, 1000);
    }

    public static function flotantesInvalidos(): array
    {
        return [
            'negativo' => [-0.1],
            'mayor al maximo' => [1000.1],
            'no numerico' => ['abc'],
        ];
    }

    public function testInputDateAceptaFechaValidaEnFormatoIso(): void
    {
        $this->assertSame('2026-03-15', input_date(['fecha' => '2026-03-15'], 'fecha'));
    }

    /** @dataProvider fechasInvalidas */
    public function testInputDateRechazaFechasInvalidas(string $fecha): void
    {
        $this->expectException(InvalidArgumentException::class);
        input_date(['fecha' => $fecha], 'fecha');
    }

    public static function fechasInvalidas(): array
    {
        return [
            'formato dd/mm/yyyy' => ['15/03/2026'],
            'fecha inexistente en el calendario' => ['2026-02-30'],
            'texto arbitrario' => ['no-es-fecha'],
        ];
    }
}
