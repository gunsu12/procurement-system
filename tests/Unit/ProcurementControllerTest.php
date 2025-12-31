<?php

namespace Tests\Unit;

use App\Http\Controllers\ProcurementController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ProcurementControllerTest extends TestCase
{
    /**
     * @dataProvider nextStatusProvider
     */
    public function test_get_next_status($currentStatus, $role, $requestType, $totalAmount, $expectedStatus)
    {
        $controller = new ProcurementController();
        $method = new ReflectionMethod(ProcurementController::class, 'getNextStatus');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $currentStatus, $role, $requestType, $totalAmount);

        $this->assertEquals($expectedStatus, $result);
    }

    public static function nextStatusProvider()
    {
        return [
            // Asset chain (Full)
            ['submitted', 'manager', 'aset', 500000, 'approved_by_manager'],
            ['approved_by_manager', 'budgeting', 'aset', 500000, 'approved_by_budgeting'],
            ['approved_by_gen_dir_holding', 'purchasing', 'aset', 500000, 'processing'],

            // Non-Asset >= 1M (Full)
            ['submitted', 'manager', 'nonaset', 1000000, 'approved_by_manager'],
            ['approved_by_gen_dir_holding', 'purchasing', 'nonaset', 1000000, 'processing'],

            // Non-Asset < 1M (Short)
            ['submitted', 'manager', 'nonaset', 500000, 'approved_by_manager'],
            ['approved_by_manager', 'budgeting', 'nonaset', 500000, 'approved_by_budgeting'],
            ['approved_by_budgeting', 'purchasing', 'nonaset', 500000, 'processing'], // Skip dir, fin mgr, fin dir, gen dir

            // Boundary cases
            ['processing', 'purchasing', 'nonaset', 500000, 'completed'],
            ['submitted', 'invalid_role', 'aset', 500000, null],
        ];
    }
}
