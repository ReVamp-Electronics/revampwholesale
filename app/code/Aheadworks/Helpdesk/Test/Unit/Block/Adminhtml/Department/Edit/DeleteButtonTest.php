<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Adminhtml\Department\Edit;

use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Block\Adminhtml\Department\Edit\DeleteButton;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;

/**
 * Test for \Aheadworks\Helpdesk\Block\Adminhtml\Department\Edit\DeleteButton
 */
class DeleteButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeleteButton
     */
    private $button;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

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

        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'request' => $this->requestMock
            ]
        );

        $this->departmentRepositoryMock = $this->getMockForAbstractClass(DepartmentRepositoryInterface::class);

        $this->button = $objectManager->getObject(
            DeleteButton::class,
            [
                'context' => $this->contextMock,
                'departmentRepository' => $this->departmentRepositoryMock
            ]
        );
    }

    /**
     * Test getButtonData method
     */
    public function testGetButtonData()
    {
        $departmentId = 1;
        $deleteUrl =
            'https://ecommerce.aheadworks.com/index.php/admin/aw_helpdesk/department/delete/id/' . $departmentId;

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with(
                $this->equalTo('*/*/delete'),
                $this->equalTo(['id' => $departmentId])
            )->willReturn($deleteUrl);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($departmentId);

        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);
        $this->departmentRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($departmentId)
            ->willReturn($departmentMock);

        $this->assertTrue(is_array($this->button->getButtonData()));
    }
}
