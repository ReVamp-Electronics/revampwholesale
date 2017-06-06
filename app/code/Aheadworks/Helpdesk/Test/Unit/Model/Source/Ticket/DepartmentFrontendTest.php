<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Model\Source\Ticket;

use Aheadworks\Helpdesk\Model\Source\Ticket\DepartmentFrontend;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\CollectionFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\Collection as DepartmentCollection;
use Aheadworks\Helpdesk\Model\Department as DepartmentModel;
use Aheadworks\Helpdesk\Api\Data\DepartmentStoreLabelInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Test for \Aheadworks\Helpdesk\Model\Source\Ticket\DepartmentFrontend
 */
class DepartmentFrontendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DepartmentFrontend
     */
    private $sourceModel;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $departmentCollectionFactoryMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->departmentCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->sourceModel = $objectManager->getObject(
            DepartmentFrontend::class,
            [
                'departmentCollectionFactory' => $this->departmentCollectionFactoryMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $websiteId = 1;
        $storeId = 2;
        $departmentId = 2;
        $departmentLabel = 'Test department label';

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($storeId);
        $storeMock->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $departmentModelMock = $this->getMockBuilder(DepartmentModel::class)
            ->setMethods(['getId', 'getName', 'getStoreLabels'])
            ->disableOriginalConstructor()
            ->getMock();
        $departmentModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($departmentId);
        $departmentModelMock->expects($this->atLeastOnce())
            ->method('getStoreLabels')
            ->willReturn([
                [
                    DepartmentStoreLabelInterface::STORE_ID => $storeId,
                    DepartmentStoreLabelInterface::LABEL => $departmentLabel
                ]
            ]);

        $collectionMock = $this->getMock(DepartmentCollection::class, [], [], '', false);
        $collectionMock->expects($this->atLeastOnce())
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $collectionMock->expects($this->atLeastOnce())
            ->method('addWebsiteFilter')
            ->with($websiteId)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$departmentModelMock]));

        $this->departmentCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);

        $options = [
            ['label' => $departmentLabel, 'value' => $departmentId]
        ];
        $this->assertEquals($options, $this->sourceModel->toOptionArray());
    }

    /**
     * Test getOptions method
     */
    public function testGetOptions()
    {
        $websiteId = 1;
        $storeId = 2;
        $departmentId = 2;
        $departmentLabel = 'Test department label';

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($storeId);
        $storeMock->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $departmentModelMock = $this->getMockBuilder(DepartmentModel::class)
            ->setMethods(['getId', 'getName', 'getStoreLabels'])
            ->disableOriginalConstructor()
            ->getMock();
        $departmentModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($departmentId);
        $departmentModelMock->expects($this->atLeastOnce())
            ->method('getStoreLabels')
            ->willReturn([
                [
                    DepartmentStoreLabelInterface::STORE_ID => $storeId,
                    DepartmentStoreLabelInterface::LABEL => $departmentLabel
                ]
            ]);

        $collectionMock = $this->getMock(DepartmentCollection::class, [], [], '', false);
        $collectionMock->expects($this->atLeastOnce())
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $collectionMock->expects($this->atLeastOnce())
            ->method('addWebsiteFilter')
            ->with($websiteId)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$departmentModelMock]));

        $this->departmentCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);

        $options = [
            $departmentId => $departmentLabel
        ];

        $this->assertEquals($options, $this->sourceModel->getOptions());
    }

    /**
     * Test getOptionByValue method
     */
    public function testGetOptionByValue()
    {
        $websiteId = 1;
        $storeId = 2;
        $departmentId = 2;
        $departmentLabel = 'Test department label';

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($storeId);
        $storeMock->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $departmentModelMock = $this->getMockBuilder(DepartmentModel::class)
            ->setMethods(['getId', 'getName', 'getStoreLabels'])
            ->disableOriginalConstructor()
            ->getMock();
        $departmentModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($departmentId);
        $departmentModelMock->expects($this->atLeastOnce())
            ->method('getStoreLabels')
            ->willReturn([
                [
                    DepartmentStoreLabelInterface::STORE_ID => $storeId,
                    DepartmentStoreLabelInterface::LABEL => $departmentLabel
                ]
            ]);

        $collectionMock = $this->getMock(DepartmentCollection::class, [], [], '', false);
        $collectionMock->expects($this->atLeastOnce())
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $collectionMock->expects($this->atLeastOnce())
            ->method('addWebsiteFilter')
            ->with($websiteId)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$departmentModelMock]));

        $this->departmentCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);

        $this->assertEquals($departmentLabel, $this->sourceModel->getOptionByValue($departmentId));
    }
}
