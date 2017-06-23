<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Block\Data\Form\Element;

use Magento\Framework\Escaper;

class Selectimg extends \Magento\Framework\Data\Form\Element\Radios
{
    /**
     * @var \Amasty\CustomerAttributes\Helper\Image
     */
    private $imageHelper;

    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data,
        \Amasty\CustomerAttributes\Helper\Image $imageHelper
    )
    {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param array $option
     * @param array $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected)
    {
        $html = '<div class="amorderattr_img_radio" style="display: inline-block; padding-right: 4px;">';
        $icon = $this->imageHelper->getIconUrl($option['value']);
        if ($icon) {
            $html .= '<img onclick="
            jQuery(this).parent().find(\'input\').click();
            " src="' . $icon
                . '" style="clear: right;" />';
        }

        $html .= '<div class="admin__field admin__field-option">' .
            '<input type="radio" ' . $this->getRadioButtonAttributes($option);
        if (is_array($option)) {
            $html .= 'value="' . $this->_escape($option['value'])
                . '" class="admin__control-radio" id="' . $this->getHtmlId() . $option['value'] . '"';
            if ($option['value'] == $selected) {
                $html .= ' checked="checked"';
            }
            $html .= ' />';
            $html .= '<label class="admin__field-label" for="' .
                $this->getHtmlId() .
                $option['value'] .
                '"><span>' .
                $option['label'] .
                '</span></label>';
        } elseif ($option instanceof \Magento\Framework\DataObject) {
            $html .= 'id="' . $this->getHtmlId() . $option->getValue() . '"'
                . $option->serialize(['label', 'title', 'value', 'class', 'style']);
            if (in_array($option->getValue(), $selected)) {
                $html .= ' checked="checked"';
            }
            $html .= ' />';
            $html .= '<label class="inline" for="' .
                $this->getHtmlId() .
                $option->getValue() .
                '">' .
                $option->getLabel() .
                '</label>';
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
}
