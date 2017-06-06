<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Test\Unit\Block\Adminhtml\Department\Edit;

use Aheadworks\Helpdesk\Block\Adminhtml\Department\Edit\SaveButton;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Helpdesk\Block\Adminhtml\Department\Edit\SaveButton
 */
class SaveButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SaveButton
     */
    private $button;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->button = $objectManager->getObject(
            SaveButton::class,
            []
        );
    }

    /**
     * Test getButtonData method
     */
    public function testGetButtonData()
    {
        $this->assertTrue(is_array($this->button->getButtonData()));
    }
}
