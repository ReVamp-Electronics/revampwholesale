<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Block\Data\Form\Element;

use Magento\Framework\Escaper;

class Multiselectimg extends \Magento\Framework\Data\Form\Element\Checkboxes
{
    /**
     * @var \Amasty\CustomerAttributes\Helper\Image
     */
    private $imageHelper;

    /**
     * Multiselectimg constructor.
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @param \Amasty\CustomerAttributes\Helper\Image $imageHelper
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\ CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data,
        \Amasty\CustomerAttributes\Helper\Image $imageHelper
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param mixed $value
     * @return string|void
     */
    public function getChecked($value)
    {
        $checked = $this->getValue()? $this->getValue(): $this->getData('checked');

        if (!is_array($checked)) {
            $checked = explode(',', $checked);
        } else {
            foreach ($checked as $k => $v) {
                $checked[$k] = (string)($v);
            }
        }
        if (in_array((string)($value), $checked)) {
            return 'checked';
        }
    }

    /**
     * @param array $option
     * @return string
     */
    protected function _optionToHtml($option)
    {
        $id = $this->getHtmlId() . '_' . $this->_escape($option['value']);

        $html = '<div class="amorderattr_img_checkbox" style="display: inline-block; padding-right: 4px;">';
        $icon = $this->imageHelper->getIconUrl($option['value']);
        if ($icon) {
            $html .= '<img onclick="
            jQuery(this).parent().find(\'input\').click();
            " src="' . $icon
                . '" style="clear: right;" />';
        }

        $html .= '<div class="field choice admin__field admin__field-option"><input id="' . $id . '"';
        foreach ($this->getHtmlAttributes() as $attribute) {
            if ($value = $this->getDataUsingMethod($attribute, $option['value'])) {
                if ($attribute == "name") {
                    $value .= "[]";
                }
                $html .= ' ' . $attribute . '="' . $value . '" class="admin__control-checkbox"';
            }
        }
        $html .= ' value="' .
            $option['value'] .
            '" />' .
            ' <label for="' .
            $id .
            '" class="admin__field-label"><span>' .
            $option['label'] .
            '</span></label></div>' .
            "\n";
        $html .= "</div>";
        return $html;
    }
}

