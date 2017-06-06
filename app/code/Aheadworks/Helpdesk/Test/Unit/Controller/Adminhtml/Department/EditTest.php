<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Controller\Adminhtml\Department;

use Aheadworks\Helpdesk\Controller\Adminhtml\Department\Edit;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;

/**
 * Test for \Aheadworks\Helpdesk\Controller\Adminhtml\Department\Edit
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Edit
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var DepartmentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $departmentRepositoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

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

        $this->resultPageFactoryMock = $this->getMock(PageFactory::class, ['create'], [], '', false);
        $this->departmentRepositoryMock = $this->getMockForAbstractClass(DepartmentRepositoryInterface::class);

        $this->controller = $objectManager->getObject(
            Edit::class,
            [
                'context' => $this->contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'departmentRepository' => $this->departmentRepositoryMock
            ]
        );
    }

    /**
     * Test execute method, new department
     */
    public function testExecuteNewDepartment()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn(null);

        $titleMock = $this->getMock(Title::class, ['prepend'], [], '', false);
        $pageConfigMock = $this->getMock(Config::class, ['getTitle'], [], '', false);
        $pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($titleMock);
        $resultPageMock = $this->getMock(Page::class, ['setActiveMenu', 'getConfig'], [], '', false);
        $resultPageMock->expects($this->any())
            ->method('setActiveMenu')
            ->willReturnSelf();
        $resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($pageConfigMock);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $this->assertSame($resultPageMock, $this->controller->execute());
    }

    /**
     * Test execute method, if department no longer exists
     */
    public function testExecuteDepartmentNoLongerExists()
    {
        $departmentId = 1;
        $exception = new NoSuchEntityException;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($departmentId);

        $this->departmentRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($departmentId)
            ->willThrowException($exception);

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception);

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
    public function testExecuteRuleExists()
    {
        $departmentId = 1;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($departmentId);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $this->departmentRepositoryMock->expects($this->once())
            ->method('getById')
            ->willReturn($departmentMock);

        $titleMock = $this->getMock(Title::class, ['prepend'], [], '', false);
        $pageConfigMock = $this->getMock(Config::class, ['getTitle'], [], '', false);
        $pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($titleMock);
        $resultPageMock = $this->getMock(Page::class, ['setActiveMenu', 'getConfig'], [], '', false);
        $resultPageMock->expects($this->any())
            ->method('setActiveMenu')
            ->willReturnSelf();
        $resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($pageConfigMock);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $this->assertSame($resultPageMock, $this->controller->execute());
    }
}
