<?php

namespace IWD\SalesRep\Block\Data\Form\Element;

use Magento\Framework\Escaper;

/**
 * Class Number
 * @package IWD\SalesRep\Block\Data\Form\Element
 */
class Number extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * Number constructor.
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('number');
    }

    /**
     * @inheritdoc
     */
    public function getElementHtml()
    {
        $html = '<input id="' . $this->getHtmlId()
            . '" type="number" '
            . '" name="' . $this->getName() . '" '
            . $this->serialize($this->getHtmlAttributes()) . $this->_getUiId()
            . 'value="' . $this->getEscapedValue() . '" ';

        if ($this->getMin() !== null) {
            $html .= ' min="' . $this->getMin() . '" ';
        }

        if ($this->getData('step') !== null) {
            $html .= ' step="' . $this->getData('step') . '" ';
        }

        $html .= ' />' . $this->getAfterElementHtml();
        return $html;
    }
}
