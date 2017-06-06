<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Controller\Adminhtml\Department;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Aheadworks\Helpdesk\Controller\Adminhtml\Department\TestConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Aheadworks\Helpdesk\Model\Gateway as GatewayModel;

/**
 * Class TestConnectionTest
 * @package Aheadworks\Helpdesk\Test\Unit\Controller\Adminhtml\Department
 */
class TestConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestConnection
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var GatewayModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $gatewayModelMock;

    /**
     * @var JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
            ]
        );

        $this->jsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->gatewayModelMock = $this->getMockBuilder(GatewayModel::class)
            ->setMethods(['testConnection'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            TestConnection::class,
            [
                'context' => $this->contextMock,
                'resultJsonFactory' => $this->jsonFactoryMock,
                'gatewayModel' => $this->gatewayModelMock
            ]
        );
    }

    /**
     * Test execute method, success connection
     */
    public function testExecuteSuccess()
    {
        $gatewayData = [
            'host'          => 'imap.gmail.com',
            'protocol'      => 'SSL',
            'login'         => 'test@gmail.com',
            'password'      => 'password',
            'port'          => '993',
            'secure_type'   => 'SSL'
        ];

        $testData = [
            'host'          => $gatewayData['host'],
            'protocol'      => $gatewayData['protocol'],
            'user'          => $gatewayData['login'],
            'password'      => $gatewayData['password'],
            'port'          => $gatewayData['port'],
            'ssl'           => $gatewayData['secure_type']
        ];

        $result = [
            'valid'         => true,
            'message'       => __('Success.')
        ];

        $jsonMock = $this->getMockBuilder(Json::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('gateway_data')
            ->willReturn($gatewayData);

        $this->gatewayModelMock->expects($this->once())
            ->method('testConnection')
            ->with($testData)
            ->willReturnSelf();

        $jsonMock->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturnSelf();

        $this->assertSame($jsonMock, $this->controller->execute());
    }

    /**
     * Test execute method, no connection
     */
    public function testExecuteNoConnection()
    {
        $gatewayData = [
            'host'          => 'imap.gmail.com',
            'protocol'      => 'SSL',
            'login'         => 'test@gmail.com',
            'password'      => 'password',
            'port'          => '993',
            'secure_type'   => 'SSL'
        ];

        $testData = [
            'host'          => $gatewayData['host'],
            'protocol'      => $gatewayData['protocol'],
            'user'          => $gatewayData['login'],
            'password'      => $gatewayData['password'],
            'port'          => $gatewayData['port'],
            'ssl'           => $gatewayData['secure_type']
        ];

        $result = [
            'valid'         => false,
            'message'       => __('cannot connect to host; error = Connection timed out (errno = 110 )')
        ];

        $jsonMock = $this->getMockBuilder(Json::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('gateway_data')
            ->willReturn($gatewayData);

        $this->gatewayModelMock->expects($this->once())
            ->method('testConnection')
            ->with($testData)
            ->willThrowException(new \Zend_Mail_Protocol_Exception(
                'cannot connect to host; error = Connection timed out (errno = 110 )'
            ));

        $jsonMock->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturnSelf();

        $this->assertSame($jsonMock, $this->controller->execute());
    }
}
