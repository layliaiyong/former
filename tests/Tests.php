<?php

namespace Lay\Former\Tests;

use Lay\Former\Tests\Demo\TestFormer;
use PHPUnit\Framework\TestCase;
use stdClass;

class Tests extends TestCase
{
    public function testFormer()
    {
        $form = new stdClass;
        $form->id = '1';
        $form->len = '2';
        $form->arr = ['3'];
        $form->tag = ['水果'];
        $obj = new stdClass;
        $obj->id = 'a';
        $objs = new stdClass;
        $objs->id = '0';
        $form->former = $obj;
        $form->formers = [$objs];

        $former = new TestFormer();
        $valid = $former->input($form)->validate();
        // echo "\n";var_dump($valid);
        echo "\n";var_dump($former->_errors);
        $this->assertNotFalse($valid);
    }
}
