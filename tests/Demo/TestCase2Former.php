<?php

namespace Lay\Former\Tests\Demo;

use Lay\Former\Former;

/**
 */
class TestCase2Former extends Former
{
    /**
     * @FormerIntVal {"message":"ID必需为数值"}
     * @FormerNotEmpty {"message":"ID不能为空"}
     */
    protected $case2 = 0;
}
