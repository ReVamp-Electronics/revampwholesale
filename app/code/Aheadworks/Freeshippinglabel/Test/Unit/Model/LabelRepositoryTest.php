<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Freeshippinglabel\Test\Unit\Model;

use Aheadworks\Freeshippinglabel\Api\Data\LabelInterface;
use Aheadworks\Freeshippinglabel\Model\Label;
use Aheadworks\Freeshippinglabel\Model\LabelRepository;
use Aheadworks\Freeshippinglabel\Api\Data\LabelInterfaceFactory;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Freeshippinglabel\Model\LabelFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Freeshippinglabel\Model\LabelRepository
 */
class LabelRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LabelRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var LabelFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelFactoryMock;

    /**
     * @var LabelInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelDataFactoryMock;

    /**
     * @var array
     */
    private $labelData = [ 'id' => 1];

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->entityManagerMock = $this->getMock(
            EntityManager::class,
            ['load', 'delete', 'save'],
            [],
            '',
            false
        );
        $this->labelFactoryMock = $this->getMock(
            LabelFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->labelDataFactoryMock = $this->getMock(
            LabelInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->model = $objectManager->getObject(
            LabelRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'labelFactory' => $this->labelFactoryMock,
                'labelDataFactory' => $this->labelDataFactoryMock,
            ]
        );
    }

    /**
     * Testing of save method
     */
    public function testSave()
    {
        $labelMock = $this->getMock(
            Label::class,
            ['getId', 'getData'],
            [],
            '',
            false
        );
        $labelMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->labelData['id']);
        $labelMock->expects($this->once())
            ->method('getData')
            ->willReturn($this->labelData);
        $labelModelMock = $this->getMock(
            Label::class,
            ['addData'],
            [],
            '',
            false
        );
        $this->labelFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($labelModelMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($labelModelMock, $this->labelData['id']);
        $labelModelMock->expects($this->once())
            ->method('addData');
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($labelModelMock);
        $this->assertSame($labelMock, $this->model->save($labelMock));
    }

    /**
     * Testing of get method
     */
    public function testGet()
    {
        $labelModelMock = $this->getMock(
            Label::class,
            ['getId'],
            [],
            '',
            false
        );
        $this->labelDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($labelModelMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($labelModelMock, $this->labelData['id']);
        $labelModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->labelData['id']);
        $this->assertSame($labelModelMock, $this->model->get($this->labelData['id']));
    }
}
