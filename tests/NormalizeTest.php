<?php

namespace Tumainimosha\MpesaPush\Tests;

use PHPUnit\Framework\TestCase;
use Tumainimosha\MpesaPush\MpesaPush;

class NormalizeTest extends TestCase
{
    /**
     * @dataProvider msisdns
     */
    public function test_normalizes_msisdn_to_255_digits(string $input, string $expected): void
    {
        $this->assertSame($expected, MpesaPush::normalizeMsisdn($input));
    }

    public static function msisdns(): array
    {
        return [
            'plus prefix' => ['+255754000000', '255754000000'],
            'already normalized' => ['255754000000', '255754000000'],
            'local with leading zero' => ['0754000000', '255754000000'],
            'bare nine digits' => ['754000000', '255754000000'],
            'spaces and dashes' => ['+255 754-000-000', '255754000000'],
        ];
    }

    /**
     * @dataProvider currencies
     */
    public function test_normalizes_currency(?string $input, string $expected): void
    {
        $this->assertSame($expected, MpesaPush::normalizeCurrency($input));
    }

    public static function currencies(): array
    {
        return [
            'legacy TSH' => ['TSH', 'TZS'],
            'lowercase legacy' => ['tsh', 'TZS'],
            'already TZS' => ['TZS', 'TZS'],
            'missing' => [null, 'TZS'],
            'empty' => ['', 'TZS'],
            'other code kept' => ['USD', 'USD'],
        ];
    }
}
