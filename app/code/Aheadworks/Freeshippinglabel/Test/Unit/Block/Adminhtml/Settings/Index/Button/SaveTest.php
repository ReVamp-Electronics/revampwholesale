<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Freeshippinglabel\Test\Unit\Block\Adminhtml\Settings\Index\Button;

use Aheadworks\Freeshippinglabel\Block\Adminhtml\Settings\Index\Button\Save;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Freeshippinglabel\Block\Adminhtml\Settings\Index\Button\Save
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Save
     */
    private $block;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(
            Save::class,
            []
        );
    }

    /**
     * Testing of getButtonData method
     */
    public function testGetButtonData()
    {
        $this->assertTrue(is_array($this->block->getButtonData()));
    }
}
