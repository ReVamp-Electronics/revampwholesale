<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Controller\Adminhtml\Department;

use Aheadworks\Helpdesk\Controller\Adminhtml\Department\MassEnable;
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

/**
 * Test for \Aheadworks\Helpdesk\Controller\Adminhtml\Department\MassEnable
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassEnableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MassEnable
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

        $this->controller = $objectManager->getObject(
            MassEnable::class,
            [
                'context' => $this->contextMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'filter' => $this->filterMock,
                'departmentRepository' => $this->departmentRepositoryMock
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

        $departmentDataObjectMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $departmentDataObjectMock->expects($this->once())
            ->method('getId')
            ->willReturn($departmentId);
        $departmentDataObjectMock->expects($this->once())
            ->method('setIsEnabled')
            ->with(true)
            ->willReturnSelf();
        $this->departmentRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($departmentId)
            ->willReturn($departmentDataObjectMock);
        $this->departmentRepositoryMock->expects($this->once())
            ->method('save')
            ->with($departmentDataObjectMock)
            ->willReturn($departmentDataObjectMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 department(s) have been enabled', $count))
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

        $this->departmentRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($departmentId)
            ->willThrowException($exception);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Something went wrong while enabling department(s)'))
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
