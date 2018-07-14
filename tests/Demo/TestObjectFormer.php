<?php

namespace Lay\Former\Tests\Demo;

use Lay\Former\Former;

/**
 */
class TestObjectFormer extends Former
{
    /**
     * @FormerIntVal {"message":"ID必需为数值"}
     * @FormerNotEmpty {"message":"ID不能为空"}
     */
    protected $id = 0;
}
