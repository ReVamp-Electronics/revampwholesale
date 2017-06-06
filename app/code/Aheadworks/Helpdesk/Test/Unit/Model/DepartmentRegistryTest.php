<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Model;

use Aheadworks\Helpdesk\Model\DepartmentRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;

/**
 * Test for \Aheadworks\Helpdesk\Model\DepartmentRegistry
 */
class DepartmentRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DepartmentRegistry
     */
    private $model;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            DepartmentRegistry::class,
            []
        );
    }

    /**
     * Test retrieve method on null
     */
    public function testRetrieveNull()
    {
        $departmentId = 1;
        $this->assertNull($this->model->retrieve($departmentId));
    }

    /**
     * Test retrieve method on object
     */
    public function testRetrieveObject()
    {
        $departmentId = 1;
        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);

        $departmentMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($departmentId);

        $this->model->push($departmentMock);

        $this->assertEquals($departmentMock, $this->model->retrieve($departmentId));
    }

    /**
     * Test remove method
     */
    public function testRemove()
    {
        $departmentId = 1;
        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);

        $departmentMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($departmentId);

        $this->model->push($departmentMock);

        $departmentFromReg = $this->model->retrieve($departmentId);
        $this->assertEquals($departmentMock, $departmentFromReg);

        $this->model->remove($departmentId);
        $this->assertNull($this->model->retrieve($departmentId));
    }

    /**
     * Test push method
     */
    public function testPush()
    {
        $departmentId = 1;
        $departmentMock = $this->getMockForAbstractClass(DepartmentInterface::class);

        $departmentMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($departmentId);

        $this->assertEquals($this->model, $this->model->push($departmentMock));

        $this->assertEquals($departmentMock, $this->model->retrieve($departmentId));
    }
}
