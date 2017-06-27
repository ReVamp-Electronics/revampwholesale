<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Test\Unit\Model;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aheadworks\Rma\Model\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    protected function setUp()
    {
        $this->model = $this->getMockBuilder('Aheadworks\Rma\Model\Status')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock()
        ;
    }

    public function testGetStoreIdInitial()
    {
        $this->assertNull(
            $this->model->getStoreId(),
            "'getStoreId()' should return null in initial state."
        );
    }

    public function testGetStoreId()
    {
        $dummyStoreId = 1;
        $this->model->setStoreId($dummyStoreId);
        $this->assertEquals(
            $dummyStoreId,
            $this->model->getStoreId(),
            "'getStoreId()' returns invalid value."
        );
    }
}
