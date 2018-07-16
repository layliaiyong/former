<?php

namespace Lay\Former\Tests\Demo;

use Lay\Former\Former;

/**
 */
class TestCase1Former extends Former
{
    /**
     * @FormerIntVal {"message":"Case1必需为数值"}
     * @FormerNotEmpty {"message":"Case1不能为空"}
     */
    protected $case1 = 0;
}
