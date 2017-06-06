<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Controller\Adminhtml\Department;

use Aheadworks\Helpdesk\Controller\Adminhtml\Department\Delete;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\TicketRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Helpdesk\Api\Data\TicketSearchResultsInterface;

/**
 * Test for \Aheadworks\Helpdesk\Controller\Adminhtml\Department\Delete
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Delete
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

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

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->resultRedirectFactoryMock = $this->getMock(RedirectFactory::class, ['create'], [], '', false);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );

        $this->departmentRepositoryMock = $this->getMockForAbstractClass(DepartmentRepositoryInterface::class);
        $this->ticketRepositoryMock = $this->getMockForAbstractClass(TicketRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['create', 'addFilter'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            Delete::class,
            [
                'context' => $this->contextMock,
                'departmentRepository' => $this->departmentRepositoryMock,
                'ticketRepository' => $this->ticketRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock
            ]
        );
    }

    /**
     * Test execute method, if no id specified
     */
    public function testExecuteIfNoIdSpecified()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn(null);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Department can\'t be deleted'));

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
     * Test execute method, if department no longer exists
     */
    public function testExecuteIfDepartmentNoLongerExists()
    {
        $departmentId = 1;
        $exception = new NoSuchEntityException;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($departmentId);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $departmentMock->expects($this->once())
            ->method('getIsDefault')
            ->willReturn(false);
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
            ->method('addErrorMessage');

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
     * Test execute method, if department exists
     */
    public function testExecuteIfDepartmentExists()
    {
        $departmentId = 1;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($departmentId);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $departmentMock->expects($this->once())
            ->method('getIsDefault')
            ->willReturn(false);
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
            ->with(__('Department was successfully deleted'));

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
     * Test execute method, if department exists and default
     */
    public function testExecuteIfDepartmentExistsAndDefault()
    {
        $departmentId = 1;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($departmentId);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $departmentMock->expects($this->once())
            ->method('getIsDefault')
            ->willReturn(true);
        $this->departmentRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($departmentId)
            ->willReturn($departmentMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Default department can not be deleted'));

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
