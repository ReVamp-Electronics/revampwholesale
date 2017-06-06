<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Controller\Adminhtml\Department;

use Aheadworks\Helpdesk\Controller\Adminhtml\Department\Index;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;

/**
 * Test for \Aheadworks\Helpdesk\Test\Unit\Controller\Adminhtml\Department\Index
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Index
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

        $this->resultPageFactoryMock = $this->getMock(PageFactory::class, ['create'], [], '', false);

        $this->controller = $objectManager->getObject(
            Index::class,
            [
                'context' => $this->contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $titleMock = $this->getMock(Title::class, ['prepend'], [], '', false);
        $pageConfigMock = $this->getMock(Config::class, ['getTitle'], [], '', false);
        $pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($titleMock);
        $resultPageMock = $this->getMock(Page::class, ['setActiveMenu', 'getConfig'], [], '', false);
        $resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->willReturnSelf();
        $resultPageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($pageConfigMock);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $this->assertSame($resultPageMock, $this->controller->execute());
    }
}
