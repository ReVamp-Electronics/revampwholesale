<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Controller\Adminhtml\Department;

use Aheadworks\Helpdesk\Controller\Adminhtml\Department\MassDelete;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\Collection as DepartmentCollection;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\CollectionFactory as DepartmentCollectionFactory;
use Aheadworks\Helpdesk\Model\Department as DepartmentModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Helpdesk\Api\TicketRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Helpdesk\Api\Data\TicketSearchResultsInterface;

/**
 * Test for \Aheadworks\Helpdesk\Controller\Adminhtml\Department\MassDelete
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassDeleteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MassDelete
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var DepartmentCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var DepartmentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $departmentRepositoryMock;

    /**
     * @var TicketRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ticketRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->resultRedirectFactoryMock = $this->getMock(RedirectFactory::class, ['create'], [], '', false);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );

        $this->collectionFactoryMock = $this->getMock(DepartmentCollectionFactory::class, ['create'], [], '', false);
        $this->filterMock = $this->getMock(Filter::class, ['getCollection'], [], '', false);
        $this->departmentRepositoryMock = $this->getMockForAbstractClass(DepartmentRepositoryInterface::class);
        $this->ticketRepositoryMock = $this->getMockForAbstractClass(TicketRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['create', 'addFilter'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            MassDelete::class,
            [
                'context' => $this->contextMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'filter' => $this->filterMock,
                'departmentRepository' => $this->departmentRepositoryMock,
                'ticketRepository' => $this->ticketRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $departmentId = 2;
        $count = 1;

        $departmentModelMock = $this->getMock(DepartmentModel::class, ['getId'], [], '', false);
        $departmentModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($departmentId);

        $collectionMock = $this->getMock(DepartmentCollection::class, ['getItems'], [], '', false);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$departmentModelMock]);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $departmentMock->expects($this->once())
            ->method('getIsDefault')
            ->willReturn(false);
        $departmentMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($departmentId);
        $this->departmentRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($departmentId)
            ->willReturn($departmentMock);
        $this->departmentRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($departmentId)
            ->willReturn(true);

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $searchResultMock = $this->getMockForAbstractClass(TicketSearchResultsInterface::class);
        $searchResultMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(0);
        $this->ticketRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 department(s) have been deleted', $count))
            ->willReturnSelf();

        $resultRedirectMock = $this->getMock(Redirect::class, ['setPath'], [], '', false);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/index')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }

    /**
     * Test execute method If default department should be deleted
     */
    public function testExecuteIfDefaultDepartment()
    {
        $departmentId = 2;
        $departmentName = 'Default department';

        $departmentModelMock = $this->getMock(DepartmentModel::class, ['getId'], [], '', false);
        $departmentModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($departmentId);

        $collectionMock = $this->getMock(DepartmentCollection::class, ['getItems'], [], '', false);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$departmentModelMock]);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $departmentMock->expects($this->once())
            ->method('getIsDefault')
            ->willReturn(true);
        $departmentMock->expects($this->once())
            ->method('getName')
            ->willReturn($departmentName);
        $this->departmentRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($departmentId)
            ->willReturn($departmentMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Default department %1 can not be deleted', $departmentName))
            ->willReturnSelf();

        $resultRedirectMock = $this->getMock(Redirect::class, ['setPath'], [], '', false);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/index')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }

    /**
     * Test execute method If an error occurs
     */
    public function testExecuteErrorOccurs()
    {
        $departmentId = 2;
        $exception = new NoSuchEntityException();

        $departmentModelMock = $this->getMock(DepartmentModel::class, ['getId'], [], '', false);
        $departmentModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($departmentId);

        $collectionMock = $this->getMock(DepartmentCollection::class, ['getItems'], [], '', false);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$departmentModelMock]);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $departmentMock->expects($this->once())
            ->method('getIsDefault')
            ->willReturn(false);
        $departmentMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($departmentId);
        $this->departmentRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($departmentId)
            ->willReturn($departmentMock);
        $this->departmentRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($departmentId)
            ->willThrowException($exception);

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $searchResultMock = $this->getMockForAbstractClass(TicketSearchResultsInterface::class);
        $searchResultMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(0);
        $this->ticketRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Something went wrong while deleting department(s)'))
            ->willReturnSelf();

        $resultRedirectMock = $this->getMock(Redirect::class, ['setPath'], [], '', false);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/index')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }
}
