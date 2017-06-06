<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Controller\Adminhtml\Department;

use Aheadworks\Helpdesk\Controller\Adminhtml\Department\Save;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Test for \Aheadworks\Helpdesk\Controller\Adminhtml\Department\Save
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Save
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
     * @var DepartmentInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $departmentFactoryMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPersistorMock;

    /**
     * @var array
     */
    private $formData = [
        'id' => '1',
        'name' => 'Test department',
        'is_enabled' => '1',
        'is_visible' => '1',
        'is_default' => '',
        'website_ids' => [
            0 => '1'
        ],
        'store_labels' => [
            0 => [
                'record_id' => '0',
                'store_id' => '1',
                'label' => 'Store Label 1'

            ],
            1 => [
                'record_id' => '1',
                'store_id' => '2',
                'label' => 'Store Label 2'
            ]
        ]
    ];

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getPostValue']
        );
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
        $this->departmentFactoryMock = $this->getMock(DepartmentInterfaceFactory::class, ['create'], [], '', false);
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataPersistorMock = $this->getMockForAbstractClass(DataPersistorInterface::class);

        $this->controller = $objectManager->getObject(
            Save::class,
            [
                'context' => $this->contextMock,
                'departmentRepository' => $this->departmentRepositoryMock,
                'departmentFactory' => $this->departmentFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'dataPersistor' => $this->dataPersistorMock
            ]
        );
    }

    /**
     * Test execute method, redirect if get data from form is empty
     */
    public function testExecuteRedirect()
    {
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn(null);

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
     * Testing of execute method, redirect if error is occured
     */
    public function testExecuteRedirectError()
    {
        $exception = new \Exception;

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($this->formData);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $this->departmentRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($this->formData['id'])
            ->willReturn($departmentMock);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray');
        $this->departmentRepositoryMock->expects($this->once())
            ->method('save')
            ->with($departmentMock)
            ->willThrowException($exception);

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception);
        $resultRedirectMock = $this->getMock(Redirect::class, ['setPath'], [], '', false);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }

    /**
     * Testing of execute method, successful save of edited department
     */
    public function testExecuteSuccesfulSave()
    {
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($this->formData);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray');
        $this->departmentRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($this->formData['id'])
            ->willReturn($departmentMock);
        $this->departmentRepositoryMock->expects($this->once())
            ->method('save')
            ->with($departmentMock)
            ->willReturn($departmentMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('Department was successfully saved'));
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
     * Testing of execute method, successful save of new department
     */
    public function testExecuteSuccesfulSaveNewRule()
    {
        unset($this->formData['id']);

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($this->formData);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray');
        $this->departmentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($departmentMock);
        $this->departmentRepositoryMock->expects($this->once())
            ->method('save')
            ->with($departmentMock)
            ->willReturn($departmentMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('Department was successfully saved'));
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
