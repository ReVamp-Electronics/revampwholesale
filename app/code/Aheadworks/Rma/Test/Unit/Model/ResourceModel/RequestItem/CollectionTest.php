<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Test\Unit\Model\ResourceModel\RequestItem;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\RequestItem\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    protected function setUp()
    {
        $select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $connection = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $connection->expects($this->any())->method('select')->willReturn($select);
        $resource = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getMainTable', 'getTable'])
            ->getMockForAbstractClass()
        ;
        $resource->expects($this->any())->method('getConnection')->willReturn($connection);
        $resource->expects($this->any())->method('getMainTable')->willReturn('table_test');
        $resource->expects($this->any())->method('getTable')->willReturn('test');

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->collection = $objectManager->getObject(
            'Aheadworks\Rma\Model\ResourceModel\RequestItem\Collection',
            [
                'resource' => $resource,
                'connection' => $connection
            ]
        );
    }

   public function testAddRequestFilter()
   {
       $requestId = 1;
       $result = $this->collection->addRequestFilter($requestId);
       $this->assertSame($this->collection, $result, "'addRequestFilter()' should return collection instance.");
   }
}
