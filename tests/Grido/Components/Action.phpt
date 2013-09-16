<?php

/**
 * Test: Action.
 *
 * @author     Petr Bugyík
 * @package    Grido\Tests
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../Helper.inc.php';

use Tester\Assert,
    Grido\Grid;

class ActionTest extends Tester\TestCase
{
    function testSetElementPrototype()
    {
        Helper::grid(function(Grid $grid){
            $grid->addActionHref('edit', 'Edit')
                ->setElementPrototype(\Nette\Utils\Html::el('a')->setClass(array('action')));
        })->run();

        ob_start();
            Helper::$grid->getAction('edit')->render(array('id' => 11));
        Assert::same('<a class="action" href="/index.php?id=11&amp;action=edit&amp;presenter=Test">Edit</a>', ob_get_clean());
    }

    function testSetCustomRender()
    {
        $testRow = array('id' => 11, 'column' => 'value');
        Helper::grid(function(Grid $grid) use ($testRow) {
            $grid->addActionHref('edit', 'Edit')
                ->setCustomRender(function($row, \Nette\Utils\Html $element) use ($testRow) {
                    Assert::same($testRow, $row);
                    unset($element->class);
                    $element->setText('TEST');
                    return $element;
                });
        })->run();

        ob_start();
            Helper::$grid->getAction('edit')->render($testRow);
        Assert::same('<a href="/index.php?id=11&amp;action=edit&amp;presenter=Test">TEST</a>', ob_get_clean());
    }

    function testSetPrimaryKey()
    {
        Helper::grid(function(Grid $grid){
            $grid->addActionHref('edit', 'Edit')
                ->setPrimaryKey('primary');
        })->run();

        ob_start();
            Helper::$grid->getAction('edit')->render(array('primary' => 11));
        Assert::same('<a class="grid-action-edit btn btn-mini" href="/index.php?primary=11&amp;action=edit&amp;presenter=Test">Edit</a>', ob_get_clean());


        Assert::error(function(){
            Helper::$grid->getAction('edit')->render(array('id' => 11));
        }, 'InvalidArgumentException', "Primary key 'primary' not found.");
    }

    function testSetDisable()
    {
        Helper::grid(function(Grid $grid){
            $grid->addActionHref('delete', 'Delete')
                ->setDisable(function($row){
                    return $row['status'] == 'delete';
                });
        })->run();

        ob_start();
            Helper::$grid->getAction('delete')->render(array('id' => 2, 'status' => 'delete'));
        Assert::same('', ob_get_clean());

        ob_start();
            Helper::$grid->getAction('delete')->render(array('id' => 3, 'status' => 'published'));
        Assert::same('<a class="grid-action-delete btn btn-mini" href="/index.php?id=3&amp;action=delete&amp;presenter=Test">Delete</a>', ob_get_clean());
    }

    function testSetConfirm()
    {
        //test string
        Helper::grid(function(Grid $grid){
            $grid->addActionHref('delete', 'Delete')
                ->setConfirm('Are you sure?');
        })->run();

        ob_start();
            Helper::$grid->getAction('delete')->render(array('id' => 2));
        Assert::same('<a class="grid-action-delete btn btn-mini" data-grido-confirm="Are you sure?" href="/index.php?id=2&amp;action=delete&amp;presenter=Test">Delete</a>', ob_get_clean());

        //test callback
        $testRow = array('id' => 2, 'firstname' => 'Lucie');
        Helper::grid(function(Grid $grid) use ($testRow) {
            $grid->addActionHref('delete', 'Delete')
                ->setConfirm(function($row) use ($testRow) {
                    Assert::same($testRow, $row);
                    return "Are you sure you want to delete {$row['firstname']}?";
                });
        })->run();

        ob_start();
            Helper::$grid->getAction('delete')->render($testRow);
        Assert::same('<a class="grid-action-delete btn btn-mini" data-grido-confirm="Are you sure you want to delete Lucie?" href="/index.php?id=2&amp;action=delete&amp;presenter=Test">Delete</a>', ob_get_clean());
    }

    function testSetIcon()
    {
        Helper::grid(function(Grid $grid){
            $grid->addActionHref('delete', 'Delete')
                ->setIcon('delete');
        })->run();

        ob_start();
            Helper::$grid->getAction('delete')->render(array('id' => 2));
        Assert::same('<a class="grid-action-delete btn btn-mini" href="/index.php?id=2&amp;action=delete&amp;presenter=Test"><i class="icon-delete"></i> Delete</a>', ob_get_clean());
    }

    /**********************************************************************************************/

    function testHasActions()
    {
        $grid = new Grid;
        Assert::false($grid->hasActions());

        $grid->addActionHref('action', 'Action');
        Assert::false($grid->hasActions());
        Assert::true($grid->hasActions(FALSE));
    }

    function testAddAction() //addAction*()
    {
        $grid = new Grid;
        $label = 'Action';

        $name = 'href';
        $destination = 'edit';
        $args = array('args');
        $grid->addActionHref($name, $label, $destination, $args);
        $component = $grid->getAction($name);
        Assert::type('\Grido\Components\Actions\Href', $component);
        Assert::type('\Grido\Components\Actions\Action', $component);
        Assert::same($label, $component->label);
        Assert::same($destination, $component->destination);
        Assert::same($args, $component->arguments);

        $name = 'event';
        $onClick = function() {};
        $grid->addActionEvent($name, $label, $onClick);
        $component = $grid->getAction($name);
        Assert::type('\Grido\Components\Actions\Event', $component);
        Assert::type('\Grido\Components\Actions\Action', $component);
        Assert::same($label, $component->label);
        Assert::same(array($onClick), $component->onClick);

        // getter
        Assert::exception(function() use ($grid) {
            $grid->getAction('action');
        }, 'InvalidArgumentException', "Component with name 'action' does not exist.");
        Assert::null($grid->getAction('action', FALSE));

        $grid = new Grid;
        Assert::null($grid->getAction('action'));
    }
}

run(__FILE__);
