<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Test\Unit\Helper;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aheadworks\Rma\Helper\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderHelper;

    protected function setUp()
    {
        $this->orderHelper = $this->getMockBuilder('Aheadworks\Rma\Helper\Order')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock()
        ;
    }

    public function testGetItemMaxCount()
    {
        $orderItemMock = $this->getMock('Magento\Sales\Model\Order\Item', [], [], '', false, false, false);
        $result = $this->orderHelper->getItemMaxCount($orderItemMock);
        $this->assertTrue(is_int($result), "'getItemMaxCount()' should return value of 'int' type.");
    }

    public function testIsAllowedForOrder()
    {
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $result = $this->orderHelper->isAllowedForOrder($orderMock);
        $this->assertTrue(is_bool($result), "'isAllowedForOrder()' should return value of 'int' type.");
    }
}
