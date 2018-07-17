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
        // FormerIntVal
        $form->id = '1';
        // NotFormerIntVal
        $form->not = 'ABC';
        // FormerBetween
        $form->len = '2';
        // FormerArrayType
        $form->arr = ['3'];
        // FormerArrayVal,each=>FormerIn
        $form->tag = ['水果'];
        // FormerObjectType, Former
        $obj = new stdClass;
        $obj->id = '4';
        $form->former = $obj;
        // FormerArrayType, Formers
        $objs = new stdClass;
        $objs->id = '5';
        $form->formers = [$objs];
        // CaseFormer
        $form->case = 2;
        $form->case2 = '6';

        $former = new TestFormer();
        $valid = $former->input($form)->validate();
        // echo "\n";var_dump($valid);
        echo "\n";var_dump($former->errors());
        $this->assertNotFalse($valid);
    }
}
