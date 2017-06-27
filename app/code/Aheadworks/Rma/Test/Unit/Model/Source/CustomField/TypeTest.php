<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Test\Unit\Model\Source\CustomField;

use Aheadworks\Rma\Model\Source\CustomField\Type as CustomFieldType;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aheadworks\Rma\Model\Source\CustomField\Type
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new \Aheadworks\Rma\Model\Source\CustomField\Type();
    }

    public function testGetOptions()
    {
        $options = $this->model->getOptions();
        $this->assertEquals(true, is_array($options));
        $this->assertInstanceOf('Magento\Framework\Phrase', $options[CustomFieldType::TEXT_VALUE]);
        $this->assertEquals(CustomFieldType::TEXT_LABEL, $options[CustomFieldType::TEXT_VALUE]->getText());
        $this->assertEquals(CustomFieldType::TEXT_AREA_LABEL, $options[CustomFieldType::TEXT_AREA_VALUE]->getText());
        $this->assertEquals(CustomFieldType::SELECT_LABEL, $options[CustomFieldType::SELECT_VALUE]->getText());
        $this->assertEquals(CustomFieldType::MULTI_SELECT_LABEL, $options[CustomFieldType::MULTI_SELECT_VALUE]->getText());
    }

    public function testToOptionArray()
    {
        $optionArray = $this->model->toOptionArray();
        $this->assertEquals(true, is_array($optionArray));
        $this->assertInstanceOf('Magento\Framework\Phrase', $optionArray[1]['label']);
    }

    public function testGetOptionLabelByValue()
    {
        $this->assertInstanceOf('Magento\Framework\Phrase', $this->model->getOptionLabelByValue(CustomFieldType::TEXT_VALUE));
        $this->assertEquals(CustomFieldType::TEXT_LABEL, $this->model->getOptionLabelByValue(CustomFieldType::TEXT_VALUE)->getText());
        $this->assertEquals(CustomFieldType::TEXT_AREA_LABEL, $this->model->getOptionLabelByValue(CustomFieldType::TEXT_AREA_VALUE)->getText());
        $this->assertEquals(CustomFieldType::SELECT_LABEL, $this->model->getOptionLabelByValue(CustomFieldType::SELECT_VALUE)->getText());
        $this->assertEquals(CustomFieldType::MULTI_SELECT_LABEL, $this->model->getOptionLabelByValue(CustomFieldType::MULTI_SELECT_VALUE)->getText());
        $this->assertNull($this->model->getOptionLabelByValue('unknown'));
    }
}
