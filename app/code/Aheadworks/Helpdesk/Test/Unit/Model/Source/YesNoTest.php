<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Model\Source;

use Aheadworks\Helpdesk\Model\Source\YesNo;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Helpdesk\Model\Source\YesNo
 */
class YesNoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var YesNo
     */
    private $sourceModel;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->sourceModel = $objectManager->getObject(
            YesNo::class,
            []
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $this->assertTrue(is_array($this->sourceModel->toOptionArray()));
    }

    /**
     * Test getOptions method
     */
    public function testGetOptions()
    {
        $optionsArray = [
            YesNo::NO  => 'No',
            YesNo::YES => 'Yes'
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
            [YesNo::NO, 'No'],
            [YesNo::YES, 'Yes']
        ];
    }
}
