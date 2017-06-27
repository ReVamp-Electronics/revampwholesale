<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Test\Unit\Model;

class CustomFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aheadworks\Rma\Model\CustomField|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\CustomField|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceModel;

    protected function setUp()
    {
        $this->model = $this->getMockBuilder('Aheadworks\Rma\Model\CustomField')
            ->disableOriginalConstructor()
            ->setMethods(['getResource'])
            ->getMock()
        ;
        $this->resourceModel = $this->getMockBuilder('Aheadworks\Rma\Model\ResourceModel\CustomField')
            ->disableOriginalConstructor()
            ->setMethods(['unserializeFields'])
            ->getMock()
        ;
        $this->model->expects($this->any())->method('getResource')->willReturn($this->resourceModel);
        $this->resourceModel->expects($this->any())->method('unserializeFields');
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

    public function testUnserializeFields()
    {
        $this->assertInstanceOf(
            'Aheadworks\Rma\Model\CustomField',
            $this->model->unserializeFields(),
            "'unserializeFields()' should return instance of 'Aheadworks\\Rma\\Model\\CustomField'."
        );
    }

    public function testToOptionArray()
    {
        $optionData = ['option' => ['value' => [1 => [0 => 'Value for store ID 0', 1 => 'Value for store ID 1']]]];
        $this->assertNull(
            $this->model->toOptionArray(),
            "'toOptionArray()' should return null if object data has not been set."
        );
        $this->assertTrue(
            is_array($this->model->setData($optionData)->toOptionArray()),
            "'toOptionArray()' should return array if object data has been set."
        );
    }
}
