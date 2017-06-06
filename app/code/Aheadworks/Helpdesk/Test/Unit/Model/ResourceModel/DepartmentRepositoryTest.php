<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Model;

use Aheadworks\Helpdesk\Api\Data\DepartmentGatewayInterface;
use Aheadworks\Helpdesk\Model\ResourceModel\DepartmentRepository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentSearchResultsInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentSearchResultsInterfaceFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\Collection as DepartmentCollection;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\CollectionFactory as DepartmentCollectionFactory;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Helpdesk\Model\DepartmentRegistry;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Aheadworks\Helpdesk\Model\Department as DepartmentModel;

/**
 * Test for \Aheadworks\Helpdesk\Model\ResourceModel\DepartmentRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DepartmentRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DepartmentRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var DepartmentRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $departmentRegistryMock;

    /**
     * @var DepartmentInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $departmentFactoryMock;

    /**
     * @var DepartmentSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $departmentSearchResultsFactoryMock;

    /**
     * @var DepartmentCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $departmentCollectionFactoryMock;

    /**
     * @var JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessorMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SortOrderBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sortOrderBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['load', 'delete', 'save'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->departmentRegistryMock = $this->getMockBuilder(DepartmentRegistry::class)
            ->setMethods(['retrieve', 'remove', 'push'])
            ->getMock();

        $this->departmentFactoryMock = $this->getMockBuilder(DepartmentInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->departmentSearchResultsFactoryMock = $this->getMockBuilder(
            DepartmentSearchResultsInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->departmentCollectionFactoryMock = $this->getMockBuilder(DepartmentCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionAttributesJoinProcessorMock = $this->getMockForAbstractClass(JoinProcessorInterface::class);

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sortOrderBuilderMock = $this->getMockBuilder(SortOrderBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            DepartmentRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'departmentRegistry' => $this->departmentRegistryMock,
                'departmentFactory' => $this->departmentFactoryMock,
                'departmentSearchResultsFactory' => $this->departmentSearchResultsFactoryMock,
                'departmentCollectionFactory' => $this->departmentCollectionFactoryMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'sortOrderBuilder' => $this->sortOrderBuilderMock
            ]
        );
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($departmentMock)
            ->willReturn($departmentMock);

        $this->departmentRegistryMock->expects($this->once())
            ->method('push')
            ->with($departmentMock);

        $this->assertSame($departmentMock, $this->model->save($departmentMock));
    }

    /**
     * Test getById method
     */
    public function testGetById()
    {
        $departmentId = 1;

        $this->departmentRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($departmentId)
            ->willReturn(null);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $departmentMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($departmentId);
        $this->departmentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($departmentMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($departmentMock, $departmentId);

        $this->departmentRegistryMock->expects($this->once())
            ->method('push')
            ->with($departmentMock);

        $this->assertSame($departmentMock, $this->model->getById($departmentId));
    }

    /**
     * Test getById method, exception will thrown if department not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with departmentId = 1
     */
    public function testGetByIdNoEntityExeption()
    {
        $departmentId = 1;

        $this->departmentRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($departmentId)
            ->willReturn(null);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $departmentMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);
        $this->departmentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($departmentMock);

        $this->model->getById($departmentId);
    }

    /**
     * Test getByGatewayEmail method
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetByGatewayEmail()
    {
        $collectionSize = 1;
        $scCurrPage = 1;
        $scPageSize = 1;
        $gatewayEmail = 'test@test.tt';

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $searchResultsMock = $this->getMockForAbstractClass(DepartmentSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->departmentSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMock(DepartmentCollection::class, [], [], '', false);
        $this->departmentCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $departmentModelMock = $this->getMockBuilder(DepartmentModel::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $departmentModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'id' => 1,
                'name' => 'Test department'
            ]);

        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([]);

        $collectionMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with($collectionSize);

        $searchCriteriaMock->expects($this->atLeastOnce())
            ->method('getSortOrders')
            ->willReturn([]);

        $searchCriteriaMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn($scCurrPage);
        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with($scCurrPage)
            ->willReturn($collectionMock);
        $searchCriteriaMock->expects($this->once())
            ->method('getPageSize')
            ->willReturn($scPageSize);
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($scPageSize)
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$departmentModelMock]));

        $gatewayMock = $this->getMockForAbstractClass(DepartmentGatewayInterface::class);
        $gatewayMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($gatewayEmail);
        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $departmentMock->expects($this->once())
            ->method('getGateway')
            ->willReturn($gatewayMock);
        $this->departmentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($departmentMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$departmentMock])
            ->willReturnSelf();
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$departmentMock]);

        $this->assertSame($departmentMock, $this->model->getByGatewayEmail($gatewayEmail));
    }

    /**
     * Test getByGatewayEmail method
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with gateway_email = test@test.tt
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetByGatewayEmailNoSuchEntity()
    {
        $collectionSize = 1;
        $scCurrPage = 1;
        $scPageSize = 1;
        $gatewayEmail = 'test@test.tt';

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $searchResultsMock = $this->getMockForAbstractClass(DepartmentSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->departmentSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMock(DepartmentCollection::class, [], [], '', false);
        $this->departmentCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $departmentModelMock = $this->getMockBuilder(DepartmentModel::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $departmentModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'id' => 1,
                'name' => 'Test department'
            ]);

        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([]);

        $collectionMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with($collectionSize);

        $searchCriteriaMock->expects($this->atLeastOnce())
            ->method('getSortOrders')
            ->willReturn([]);

        $searchCriteriaMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn($scCurrPage);
        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with($scCurrPage)
            ->willReturn($collectionMock);
        $searchCriteriaMock->expects($this->once())
            ->method('getPageSize')
            ->willReturn($scPageSize);
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($scPageSize)
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$departmentModelMock]));

        $gatewayMock = $this->getMockForAbstractClass(DepartmentGatewayInterface::class);
        $gatewayMock->expects($this->once())
            ->method('getEmail')
            ->willReturn(null);
        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $departmentMock->expects($this->once())
            ->method('getGateway')
            ->willReturn($gatewayMock);
        $this->departmentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($departmentMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$departmentMock])
            ->willReturnSelf();
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$departmentMock]);

        $this->model->getByGatewayEmail($gatewayEmail);
    }

    /**
     * Test getDefaultByWebsiteId method If no default department is selected
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Default department for the website is not set
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetDefaultByWebsiteIdNotSetException()
    {
        $filterWebsite = 'website_ids';
        $websiteId = 1;
        $collectionSize = 0;
        $scCurrPage = 0;
        $scPageSize = 0;

        $sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->getMock();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($sortOrderMock);

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with($sortOrderMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $searchResultsMock = $this->getMockForAbstractClass(DepartmentSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->departmentSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMock(DepartmentCollection::class, [], [], '', false);
        $this->departmentCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);

        $filterGroupMock = $this->getMock(FilterGroup::class, [], [], '', false);
        $filterMock = $this->getMock(Filter::class, [], [], '', false);
        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupMock]);
        $filterGroupMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterMock]);
        $filterMock->expects($this->once())
            ->method('getField')
            ->willReturn($filterWebsite);
        $filterMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($websiteId);
        $collectionMock->expects($this->once())
            ->method('addWebsiteFilter')
            ->with($websiteId);
        $collectionMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with($collectionSize);
        $searchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($collectionSize);

        $sortOrderMock = $this->getMock(SortOrder::class, [], [], '', false);
        $searchCriteriaMock->expects($this->atLeastOnce())
            ->method('getSortOrders')
            ->willReturn([$sortOrderMock]);
        $sortOrderMock->expects($this->once())
            ->method('getField')
            ->willReturn($filterWebsite);
        $collectionMock->expects($this->once())
            ->method('addOrder')
            ->with($filterWebsite, SortOrder::SORT_ASC);
        $sortOrderMock->expects($this->once())
            ->method('getDirection')
            ->willReturn(SortOrder::SORT_ASC);
        $searchCriteriaMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn($scCurrPage);
        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with($scCurrPage)
            ->willReturn($collectionMock);
        $searchCriteriaMock->expects($this->once())
            ->method('getPageSize')
            ->willReturn($scPageSize);
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($scPageSize)
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([])
            ->willReturnSelf();

        $this->model->getDefaultByWebsiteId($websiteId);
    }

    /**
     * Test getDefaultByWebsiteId method
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetDefaultByWebsiteId()
    {
        $filterWebsite = 'website_ids';
        $websiteId = 1;
        $collectionSize = 1;
        $scCurrPage = 1;
        $scPageSize = 1;

        $sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->getMock();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($sortOrderMock);

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with($sortOrderMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $searchResultsMock = $this->getMockForAbstractClass(DepartmentSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->departmentSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMock(DepartmentCollection::class, [], [], '', false);
        $this->departmentCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $departmentModelMock = $this->getMockBuilder(DepartmentModel::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $departmentModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'id' => 1,
                'name' => 'Test department'
            ]);

        $filterGroupMock = $this->getMock(FilterGroup::class, [], [], '', false);
        $filterMock = $this->getMock(Filter::class, [], [], '', false);
        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupMock]);
        $filterGroupMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterMock]);
        $filterMock->expects($this->once())
            ->method('getField')
            ->willReturn($filterWebsite);
        $filterMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($websiteId);
        $collectionMock->expects($this->once())
            ->method('addWebsiteFilter')
            ->with($websiteId);
        $collectionMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with($collectionSize);
        $searchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($collectionSize);

        $sortOrderMock = $this->getMock(SortOrder::class, [], [], '', false);
        $searchCriteriaMock->expects($this->atLeastOnce())
            ->method('getSortOrders')
            ->willReturn([$sortOrderMock]);
        $sortOrderMock->expects($this->once())
            ->method('getField')
            ->willReturn($filterWebsite);
        $collectionMock->expects($this->once())
            ->method('addOrder')
            ->with($filterWebsite, SortOrder::SORT_ASC);
        $sortOrderMock->expects($this->once())
            ->method('getDirection')
            ->willReturn(SortOrder::SORT_ASC);
        $searchCriteriaMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn($scCurrPage);
        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with($scCurrPage)
            ->willReturn($collectionMock);
        $searchCriteriaMock->expects($this->once())
            ->method('getPageSize')
            ->willReturn($scPageSize);
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($scPageSize)
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$departmentModelMock]));

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $this->departmentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($departmentMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$departmentMock])
            ->willReturnSelf();
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$departmentMock]);

        $this->assertSame($departmentMock, $this->model->getDefaultByWebsiteId($websiteId));
    }

    /**
     * Test getList method
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $filterName = 'Name';
        $filterValue = 'Department';
        $collectionSize = 5;
        $scCurrPage = 1;
        $scPageSize = 3;

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $searchResultsMock = $this->getMockForAbstractClass(DepartmentSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->departmentSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMock(DepartmentCollection::class, [], [], '', false);
        $this->departmentCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $departmentModelMock = $this->getMockBuilder(DepartmentModel::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $departmentModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'id' => 1,
                'name' => 'Test department'
            ]);

        $filterGroupMock = $this->getMock(FilterGroup::class, [], [], '', false);
        $filterMock = $this->getMock(Filter::class, [], [], '', false);
        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupMock]);
        $filterGroupMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterMock]);
        $filterMock->expects($this->once())
            ->method('getConditionType')
            ->willReturn(false);
        $filterMock->expects($this->exactly(2))
            ->method('getField')
            ->willReturn($filterName);
        $filterMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($filterValue);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with([$filterName], [['eq' => $filterValue]]);
        $collectionMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with($collectionSize);

        $sortOrderMock = $this->getMock(SortOrder::class, [], [], '', false);
        $searchCriteriaMock->expects($this->atLeastOnce())
            ->method('getSortOrders')
            ->willReturn([$sortOrderMock]);
        $sortOrderMock->expects($this->once())
            ->method('getField')
            ->willReturn($filterName);
        $collectionMock->expects($this->once())
            ->method('addOrder')
            ->with($filterName, SortOrder::SORT_ASC);
        $sortOrderMock->expects($this->once())
            ->method('getDirection')
            ->willReturn(SortOrder::SORT_ASC);
        $searchCriteriaMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn($scCurrPage);
        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with($scCurrPage)
            ->willReturn($collectionMock);
        $searchCriteriaMock->expects($this->once())
            ->method('getPageSize')
            ->willReturn($scPageSize);
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($scPageSize)
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$departmentModelMock]));

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $this->departmentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($departmentMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$departmentMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        $departmentId = 1;

        $this->departmentRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($departmentId)
            ->willReturn(null);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $departmentMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($departmentId);
        $this->departmentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($departmentMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($departmentMock, $departmentId);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($departmentMock);

        $this->departmentRegistryMock->expects($this->once())
            ->method('remove')
            ->with($departmentId);

        $this->assertTrue($this->model->delete($departmentMock));
    }

    /**
     * Test deleteById method
     */
    public function testDeleteById()
    {
        $departmentId = 1;

        $this->departmentRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($departmentId)
            ->willReturn(null);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $departmentMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($departmentId);
        $this->departmentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($departmentMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($departmentMock, $departmentId);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($departmentMock);

        $this->departmentRegistryMock->expects($this->once())
            ->method('remove')
            ->with($departmentId);

        $this->assertTrue($this->model->deleteById($departmentId));
    }
}
