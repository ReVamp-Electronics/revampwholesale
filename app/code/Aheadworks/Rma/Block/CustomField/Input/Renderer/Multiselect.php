<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\CustomField\Input\Renderer;

/**
 * Class Multiselect
 * @package Aheadworks\Rma\Block\CustomField\Input\Renderer
 */
class Multiselect extends Select
{
    /**
     * @var string
     */
    protected $_template = 'customfield/input/renderer/multiselect.phtml';

    /**
     * @var array
     */
    protected $classNames = ['select', 'multiselect'];

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->getOptionArray();
    }

    /**
     * @param int $value
     * @return bool
     */
    public function isSelected($value)
    {
        if (is_array($this->getValue())) {
            return in_array($value, $this->getValue());
        }
        $default = $this->getCustomField()->getOption('default');
        if (is_array($default)) {
            return in_array($value, $default);
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        $editableForStatusIds = $this->getCustomField()->getEditableForStatusIds();
        if (!is_array($editableForStatusIds)
            || !in_array($this->getStatusId(), $editableForStatusIds)
        ) {
            return false;
        }
        $enable = $this->getCustomField()->getOption('enable');
        if ($this->getValue() && array_intersect($this->getValue(), $enable) != $this->getValue()) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (!$this->hasData('name')) {
            $this->setData('name', sprintf("custom_fields[%u][]", $this->getCustomField()->getId()));
        }
        return $this->getData('name');
    }

    /**
     * @return string
     */
    public function getHiddenName()
    {
        return sprintf("custom_fields[%u]", $this->getCustomField()->getId());
    }

    /**
     * @return string
     */
    public function getValueLabel()
    {
        if (is_array($this->getValue())) {
            $label = "";
            foreach ($this->getValue() as $value) {
                $label .= $this->getCustomField()->getOptionLabelByValue($value);
                $label .= "<br/>";
            }
            return $label;
        }
        return '';
    }
}
