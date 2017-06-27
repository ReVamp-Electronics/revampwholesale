<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\CustomField\Input\Renderer;

/**
 * Class Select
 * @package Aheadworks\Rma\Block\CustomField\Input\Renderer
 */
class Select extends RendererAbstract
{
    /**
     * @var string
     */
    protected $_template = 'customfield/input/renderer/select.phtml';

    /**
     * @var array
     */
    protected $classNames = ['select'];

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->getWithCaptions() ?
            array_merge([['value' => '', 'label' => __('-- please select --')]], $this->getOptionArray()) :
            $this->getOptionArray()
            ;
    }

    /**
     * @param int $value
     * @return bool
     */
    public function isSelected($value)
    {
        if ($this->getValue()) {
            return $value == (int)$this->getValue();
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
        if (!$this->getCustomField()->getIsSystem() && $this->getValue()) {
            $enable = $this->getCustomField()->getOption('enable');
            if (!in_array($this->getValue(), $enable)) {
                return false;
            }
        }
        return parent::isEditable();
    }

    /**
     * @return string
     */
    public function getValueLabel()
    {
        if ($this->getValue()) {
            return $this->getCustomField()->getOptionLabelByValue($this->getValue());
        }
        return '';
    }

    /**
     * @return array
     */
    protected function getOptionArray()
    {
        $optionArray = $this->getCustomField()->toOptionArray();
        return is_array($optionArray) ? $optionArray : [];
    }
}
