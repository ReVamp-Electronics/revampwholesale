<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Test\Unit\Model\Status;

class ConfigDefaultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aheadworks\Rma\Model\Status\ConfigDefault|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    protected function setUp()
    {
        $this->config = $this->getMockBuilder('Aheadworks\Rma\Model\Status\ConfigDefault')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock()
        ;
    }

    public function testDummy()
    {

    }
}
