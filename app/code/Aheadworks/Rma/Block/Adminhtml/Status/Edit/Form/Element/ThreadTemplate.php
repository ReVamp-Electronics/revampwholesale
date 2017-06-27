<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml\Status\Edit\Form\Element;

/**
 * Class ThreadTemplate
 * @package Aheadworks\Rma\Block\Adminhtml\Status\Edit\Form\Element
 */
class ThreadTemplate extends \Magento\Framework\Data\Form\Element\Textarea
{
    /**
     * @var \Aheadworks\Rma\Block\Adminhtml\Form\Element\Helper\Label
     */
    protected $helperLabel;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Aheadworks\Rma\Block\Adminhtml\Form\Element\Helper\Label $helperLabel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Aheadworks\Rma\Block\Adminhtml\Form\Element\Helper\Label $helperLabel,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->helperLabel = $helperLabel;
    }

    /**
     * @param string $idSuffix
     * @return string
     */
    public function getLabelHtml($idSuffix = '', $scopeLabel = '')
    {
        return '<label class="label admin__field-label aw-rma-element-label-stores" for="' .
            $this->getHtmlId() . $idSuffix . '"' . $this->_getUiId('label') .
            '>' . $this->helperLabel->getLabelHtml($this->getStoreId()) . '</label>' . "\n"
        ;
    }
}
