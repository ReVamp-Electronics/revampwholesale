<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Test\Unit\Model;

class RequestItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aheadworks\Rma\Model\RequestItem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    protected function setUp()
    {
        $this->model = $this->getMockBuilder('Aheadworks\Rma\Model\RequestItem')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock()
        ;
    }

    public function testDummy()
    {

    }
}
