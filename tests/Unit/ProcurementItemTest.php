<?php

namespace Tests\Unit;

use App\Models\ProcurementItem;
use PHPUnit\Framework\TestCase;

class ProcurementItemTest extends TestCase
{
    public function test_subtotal_attribute_calculates_correctly()
    {
        $item = new ProcurementItem();
        $item->quantity = 5;
        $item->estimated_price = 10000;

        $this->assertEquals(50000, $item->subtotal);
    }
}
