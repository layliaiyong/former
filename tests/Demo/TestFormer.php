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
     * @FormerBetween {"params":[1,10],"message":"LEN必须介于1~10之间"}
     */
    protected $len = 0;
    /**
     * @FormerArrayType {"message":"ARR必须是数组"}
     */
    protected $arr = [];
    /**
     * @FormerArrayVal {"params":[["水果","蔬菜","茶叶"]],"each":"FormerIn","message":"TAG必须是水果、蔬菜、茶叶的数组"}
     */
    protected $tag = [];
}
