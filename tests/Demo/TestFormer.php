<?php

namespace Lay\Former\Tests\Demo;

use Lay\Former\Former;

/**
 */
class TestFormer extends Former
{
    /**
     * @FormerIntVal {"message":"ID必需为数值"}
     */
    protected $id = 0;
    /**
     * @NotFormerIntVal {"message":"NOT必需不是数值"}
     */
    protected $not = 0;
    /**
     * @FormerBetween {"message":"LEN必须介于1~10之间","params":[1,10]}
     */
    protected $len = 0;
    /**
     * @FormerArrayType {"message":"ARR必须是数组"}
     */
    protected $arr = [];
    /**
     * @FormerArrayVal {"message":"TAG必须是水果、蔬菜、茶叶的数组","params":[["水果","蔬菜","茶叶"]],"each":"FormerIn"}
     */
    protected $tag = [];
    /**
     * @FormerObjectType {"message":"former必须是对象"}
     * @Former {"message":"former对象验证失败","former":"\\Lay\\Former\\Tests\\Demo\\TestObjectFormer"}
     */
    protected $former = null;
    /**
     * @FormerArrayType {"message":"formers必须是对象数组","each":"FormerObjectType"}
     * @Formers {"message":"formers对象数组验证失败","former":"\\Lay\\Former\\Tests\\Demo\\TestObjectFormer"}
     */
    protected $formers = [];
    /**
     * @CaseFormer [{"message":"case=1验证失败","when":"FormerEquals","params":[1],"former":"\\Lay\\Former\\Tests\\Demo\\TestCase1Former"},{"message":"case2验证失败","when":"FormerEquals","params":[2],"former":"\\Lay\\Former\\Tests\\Demo\\TestCase2Former"}]
     */
    protected $case = 1;
    protected $case1;
    protected $case2;
}
