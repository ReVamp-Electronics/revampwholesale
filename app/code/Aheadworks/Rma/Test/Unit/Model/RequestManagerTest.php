<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Test\Unit\Model;

use Aheadworks\Rma\Model\Source\Request\Status;

class RequestManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aheadworks\Rma\Model\RequestManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Aheadworks\Rma\Model\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestModel;

    /**
     * @var \Aheadworks\Rma\Model\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $statusModel;

    /**
     * @var \Aheadworks\Rma\Model\ThreadMessage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $threadMessageModel;

    /**
     * @var \Aheadworks\Rma\Model\Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sender;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $order;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Aheadworks\Rma\Helper\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderHelper;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requestModel = $this->getMockBuilder('Aheadworks\Rma\Model\Request')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'load'])
            ->getMock()
        ;
        $this->statusModel = $this->getMockBuilder('Aheadworks\Rma\Model\Status')
            ->disableOriginalConstructor()
            ->setMethods(['load'])
            ->getMock()
        ;
        $this->statusModel->expects($this->any())
            ->method('load')
            ->willReturnSelf()
        ;
        $this->threadMessageModel = $this->getMockBuilder('Aheadworks\Rma\Model\ThreadMessage')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock()
        ;
        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass()
        ;
        $requestModelFactory = $this->getMockBuilder('Aheadworks\Rma\Model\RequestFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock()
        ;
        $requestModelFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->requestModel)
        ;
        $statusModelFactory = $this->getMockBuilder('Aheadworks\Rma\Model\StatusFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock()
        ;
        $statusModelFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->statusModel)
        ;
        $threadMessageModelFactory = $this->getMockBuilder('Aheadworks\Rma\Model\ThreadMessageFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock()
        ;
        $threadMessageModelFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->threadMessageModel)
        ;
        $this->sender = $this->getMockBuilder('Aheadworks\Rma\Model\Sender')
            ->setMethods(['send'])
            ->getMock()
        ;
        $this->order = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'load', 'loadByIncrementId', 'getItemsCollection'])
            ->getMock()
        ;
        $this->order->expects($this->any())->method('loadByIncrementId')->willReturnSelf();
        $this->order->expects($this->any())->method('getItemsCollection')->willReturn([]);
        $this->order->setId(1);
        $orderFactory = $this->getMockBuilder('Magento\Sales\Model\OrderFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock()
        ;
        $orderFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->order)
        ;
        $this->customerSession = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->customerSession->expects($this->any())->method('getCustomerData')->willReturn(new \Magento\Framework\DataObject([]));
        $this->orderHelper = $this->getMockBuilder('Aheadworks\Rma\Helper\Order')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->orderHelper->expects($this->any())->method('isAllowedForOrder')->willReturn(true);
        $this->model = $this->getMockBuilder('Aheadworks\Rma\Model\RequestManager')
            ->setConstructorArgs([
                $requestModelFactory,
                $statusModelFactory,
                $threadMessageModelFactory,
                $this->sender,
                $this->scopeConfig,
                $orderFactory,
                $this->customerSession,
                $this->orderHelper
            ])
            ->setMethods(null)
            ->getMock()
        ;
    }

    /**
     * @dataProvider createData
     */
    public function testCreate($data)
    {
        $this->requestModel->expects($this->once())
            ->method('save')
            ->will($this->returnValue($this->requestModel))
        ;
        $newRequestModel = $this->model->create($data);

        $this->assertEquals(Status::PENDING_APPROVAL, $this->requestModel->getStatus(), "Status of new request should be 'Pending approval'.");
        $this->assertSame($this->requestModel, $newRequestModel, "'create()' should return created request model.");
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider createData
     */
    public function testCreateWithAbsentOrderId($data)
    {
        unset($data['order_id']);
        $this->model->create($data);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider createData
     */
    public function testCreateWithInvalidOrderId($data)
    {
        $this->order->unsId();
        $this->model->create($data);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider createData
     */
    public function testCreateWithAbsentItems($data)
    {
        unset($data['items']);
        $this->model->create($data);
    }

    public function testSetStatus()
    {
        $requestId = 1;
        $newStatus = Status::APPROVED;
        $this->requestModel->setId($requestId);
        $this->requestModel->expects($this->once())
            ->method('load')
            ->with($this->equalTo($requestId))
            ->will($this->returnValue($this->requestModel))
        ;
        $this->requestModel->expects($this->once())
            ->method('save')
            ->will($this->returnValue($this->requestModel))
        ;
        $requestModel = $this->model->setStatus($requestId, $newStatus);

        $this->assertEquals($newStatus, $this->requestModel->getStatus(), "Request status is not changed.");
        $this->assertSame($this->requestModel, $requestModel, "'setStatus()' should return request model instance.");
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testSetStatusNotExistsException()
    {
        $requestId = 1;
        $newStatus = Status::APPROVED;
        $this->requestModel->expects($this->once())
            ->method('load')
            ->with($this->equalTo($requestId))
            ->will($this->returnValue($this->requestModel))
        ;
        $this->model->setStatus($requestId, $newStatus);
    }

    public function createData()
    {
        return [
            [[
                'order_id' => '0000000001',
                'items' => [
                    1 => [
                        'qty' => 2,
                        'custom_fields' => [
                            4 => 'custom field 4 value 1',
                            5 => 'custom field 5 value 2',
                            6 => 'custom field 6 value 3',
                        ]
                    ],
                    5 => [
                        'qty' => 1,
                        'custom_fields' => [
                            4 => 'custom field 4 value 4',
                            5 => 'custom field 5 value 5',
                            6 => 'custom field 6 value 6',
                        ]
                    ]
                ],
                'custom_fields' => [
                    1 => 'resolution value',
                    2 => 'package condition value'
                ],
                'message' => [
                    'text' => 'Message to admin',
                    'attachments' => [
                        'attachment 1',
                        'attachment 2'
                    ]
                ]
            ]]
        ];
    }
}
