<?php

/**
 * Test: Number column.
 *
 * @author     Petr Bugyík
 * @package    Grido\Tests
 */

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert,
    Grido\Grid;

class ColumnNumberTest extends Tester\TestCase
{
    function testRender()
    {
        $grid = new Grid;
        $column = $grid->addColumnNumber('column', 'Column');
        Assert::same('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;a', $column->render(array('column' => '<script>alert("XSS")</script>a')));

        Assert::same('12,346', $column->render(array('column' => 12345.99)));

        $column->setNumberFormat(1, ',', '.');
        Assert::same('12.345,6', $column->render(array('column' => '12345.55')));
    }
}

run(__FILE__);
