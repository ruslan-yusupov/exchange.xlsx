<?php

namespace Tests;

use App\Barcode;
use PHPUnit\Framework\TestCase;

class ValidBarcodeTest extends TestCase
{

    /**
     * @dataProvider provider
     * @param string $barcode
     * @param bool $result
     */
    public function testBarcode(string $barcode, bool $result)
    {
        $this->assertSame($result, Barcode::isValid($barcode));
    }


    /**
     * @return array
     */
    public function provider(): array
    {
        return [
            ['2109970000015', true],
            ['2154231000019f', false],

        ];
    }

}
