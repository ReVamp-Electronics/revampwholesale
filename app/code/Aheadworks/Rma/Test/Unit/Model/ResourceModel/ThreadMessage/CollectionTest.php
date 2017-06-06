<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Test\Unit\Model\ResourceModel\ThreadMessage;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Report\Collection
     */
    protected $collection;

    protected function setUp()
    {
        $this->collection = $this->getMockBuilder('\Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Collection')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $connection = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->setMethods(['_connect', '_quote'])
            ->getMock()
        ;
        $this->collection->setConnection($connection);
    }

    public function testGetRequestThread()
    {
        $requestThreadId = 1;
        $this->assertInstanceOf('\Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Collection', $this->collection->getRequestThread($requestThreadId));
    }
}