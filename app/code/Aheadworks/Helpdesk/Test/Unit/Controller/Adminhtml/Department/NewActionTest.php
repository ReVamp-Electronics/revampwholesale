<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Controller\Adminhtml\Department;

use Aheadworks\Helpdesk\Controller\Adminhtml\Department\NewAction;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;

/**
 * Test for \Aheadworks\Helpdesk\Controller\Adminhtml\Department\NewAction
 */
class NewActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NewAction
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $forwardFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->contextMock = $objectManager->getObject(
            Context::class,
            []
        );

        $this->forwardFactoryMock = $this->getMock(ForwardFactory::class, ['create'], [], '', false);

        $this->controller = $objectManager->getObject(
            NewAction::class,
            [
                'context' => $this->contextMock,
                'resultForwardFactory' => $this->forwardFactoryMock
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $resultForwardMock = $this->getMock(Forward::class, ['forward'], [], '', false);
        $resultForwardMock->expects($this->once())
            ->method('forward')
            ->willReturnSelf();
        $this->forwardFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultForwardMock);

        $this->assertSame($resultForwardMock, $this->controller->execute());
    }
}
