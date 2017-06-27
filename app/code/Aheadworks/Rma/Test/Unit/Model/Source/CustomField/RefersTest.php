<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Test\Unit\Model\Source\CustomField;

use Aheadworks\Rma\Model\Source\CustomField\Refers as CustomFieldRefers;

class RefersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aheadworks\Rma\Model\Source\CustomField\Type
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new \Aheadworks\Rma\Model\Source\CustomField\Refers();
    }

    public function testGetOptions()
    {
        $options = $this->model->getOptions();
        $this->assertEquals(true, is_array($options));
        $this->assertInstanceOf('Magento\Framework\Phrase', $options[CustomFieldRefers::REQUEST_VALUE]);
        $this->assertEquals(CustomFieldRefers::REQUEST_LABEL, $options[CustomFieldRefers::REQUEST_VALUE]->getText());
        $this->assertEquals(CustomFieldRefers::ITEM_LABEL, $options[CustomFieldRefers::ITEM_VALUE]->getText());

    }

    public function testToOptionArray()
    {
        $optionArray = $this->model->toOptionArray();
        $this->assertEquals(true, is_array($optionArray));
        $this->assertInstanceOf('Magento\Framework\Phrase', $optionArray[1]['label']);
    }

    public function testGetOptionLabelByValue()
    {
        $this->assertInstanceOf('Magento\Framework\Phrase', $this->model->getOptionLabelByValue(CustomFieldRefers::REQUEST_VALUE));
        $this->assertEquals(CustomFieldRefers::REQUEST_LABEL, $this->model->getOptionLabelByValue(CustomFieldRefers::REQUEST_VALUE)->getText());
        $this->assertEquals(CustomFieldRefers::ITEM_LABEL, $this->model->getOptionLabelByValue(CustomFieldRefers::ITEM_VALUE)->getText());
        $this->assertNull($this->model->getOptionLabelByValue('unknown'));
    }
}
