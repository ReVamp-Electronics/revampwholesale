<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Model\Source;

use Aheadworks\Helpdesk\Model\Source\Websites;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\Convert\DataObject;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Test for \Aheadworks\Helpdesk\Model\Source\Websites
 */
class WebsitesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Websites
     */
    private $sourceModel;

    /**
     * @var WebsiteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteRepositoryMock;

    /**
     * @var DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectConverterMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->websiteRepositoryMock = $this->getMockForAbstractClass(WebsiteRepositoryInterface::class);
        $this->objectConverterMock = $this->getMock(DataObject::class, [], [], '', false);

        $this->sourceModel = $objectManager->getObject(
            Websites::class,
            [
                'websiteRepository' => $this->websiteRepositoryMock,
                'objectConverter' => $this->objectConverterMock
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $websiteId = 1;
        $websiteName = 'Website1';

        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);

        $websites = [$websiteMock];
        $this->websiteRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn($websites);

        $options = [
            ['label' => $websiteName, 'value' => $websiteId]
        ];
        $this->objectConverterMock->expects($this->once())
            ->method('toOptionArray')
            ->with($websites, 'id', 'name')
            ->willReturn($options);

        $this->assertTrue(is_array($this->sourceModel->toOptionArray()));
    }

    /**
     * Test getOptions method
     */
    public function testGetOptions()
    {
        $websiteId = 1;
        $websiteName = 'Website1';

        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);

        $websites = [$websiteMock];
        $this->websiteRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn($websites);

        $options = [
            ['label' => $websiteName, 'value' => $websiteId]
        ];
        $this->objectConverterMock->expects($this->once())
            ->method('toOptionArray')
            ->with($websites, 'id', 'name')
            ->willReturn($options);

        $optionsArray = [
            $websiteId  => $websiteName
        ];

        $this->assertEquals($optionsArray, $this->sourceModel->getOptions());
    }

    /**
     * Test getOptionByValue method
     *
     * @param int $value
     * @param string $expected
     * @dataProvider getOptionByValueDataProvider
     */
    public function testGetOptionByValue($value, $expected)
    {
        $websiteOneId = 1;
        $websiteOneName = 'Website1';
        $websiteTwoId = 2;
        $websiteTwoName = 'Website2';

        $websiteOneMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $websiteOneMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteOneId);
        $websiteTwoMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $websiteTwoMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteTwoId);

        $websites = [$websiteOneMock, $websiteTwoMock];
        $this->websiteRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn($websites);

        $options = [
            ['label' => $websiteOneName, 'value' => $websiteOneId],
            ['label' => $websiteTwoName, 'value' => $websiteTwoId]
        ];
        $this->objectConverterMock->expects($this->once())
            ->method('toOptionArray')
            ->with($websites, 'id', 'name')
            ->willReturn($options);

        $this->assertEquals($expected, $this->sourceModel->getOptionByValue($value));
    }

    /**
     * Data provider for testGetOptionByValue method
     *
     * @return array
     */
    public function getOptionByValueDataProvider()
    {
        return [
            [1, 'Website1'],
            [2, 'Website2']
        ];
    }
}
