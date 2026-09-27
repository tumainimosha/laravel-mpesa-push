<?php

namespace Tumainimosha\MpesaPush\Tests;

use PHPUnit\Framework\TestCase;
use Tumainimosha\MpesaPush\MpesaPush;

/**
 * hashPassword() is a pure function, so this doesn't need the Laravel test bootstrap.
 */
class HashPasswordTest extends TestCase
{
    public function test_matches_a_known_vector(): void
    {
        $hash = MpesaPush::hashPassword('123456', 'secret', '20200114182235');

        $this->assertSame('M0JFNTg3NkE2QkI4NTNERDhBN0NBOTlFMzE5NDI3QjQxQzkyRDI4MzY4NEJFMTRCQUU0QjIzMTg1Mzc4NUVGMA==', $hash);
    }

    public function test_changing_the_timestamp_changes_the_hash(): void
    {
        $a = MpesaPush::hashPassword('123456', 'secret', '20200114182235');
        $b = MpesaPush::hashPassword('123456', 'secret', '20200114182236');

        $this->assertNotSame($a, $b);
    }
}
