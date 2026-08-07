<?php

use PHPUnit\Framework\TestCase;

/**
 * Cubre e() de Config/helpers.php: la función de escape usada en todas las
 * salidas dinámicas del proyecto para prevenir XSS.
 */
final class EscapeTest extends TestCase
{
    /** @dataProvider valoresPeligrosos */
    public function testEscapaCaracteresPeligrosos(string $entrada, string $esperado): void
    {
        $this->assertSame($esperado, e($entrada));
    }

    public static function valoresPeligrosos(): array
    {
        return [
            'script tag' => ['<script>alert(1)</script>', '&lt;script&gt;alert(1)&lt;/script&gt;'],
            'comillas dobles' => ['"onmouseover="alert(1)', '&quot;onmouseover=&quot;alert(1)'],
            'comillas simples' => ["' OR '1'='1", '&#039; OR &#039;1&#039;=&#039;1'],
            'ampersand' => ['Vacas & Potreros', 'Vacas &amp; Potreros'],
        ];
    }

    public function testConvierteValoresNoStringATextoAntesDeEscapar(): void
    {
        $this->assertSame('123', e(123));
        $this->assertSame('1', e(true));
    }
}
