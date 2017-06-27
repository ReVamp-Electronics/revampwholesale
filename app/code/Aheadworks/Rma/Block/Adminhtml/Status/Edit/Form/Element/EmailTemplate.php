<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml\Status\Edit\Form\Element;

/**
 * Class EmailTemplate
 * @package Aheadworks\Rma\Block\Adminhtml\Status\Edit\Form\Element
 */
class EmailTemplate extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * @var \Aheadworks\Rma\Block\Adminhtml\Form\Element\Helper\Label
     */
    protected $helperLabel;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\UrlInterface $url
     * @param \Aheadworks\Rma\Block\Adminhtml\Form\Element\Helper\Label $helperLabel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\UrlInterface $url,
        \Aheadworks\Rma\Block\Adminhtml\Form\Element\Helper\Label $helperLabel,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->url = $url;
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

    /**
     * @return string
     */
    public function getElementHtml()
    {
        return parent::getElementHtml() . $this->getPreviewLinkHtml();
    }

    /**
     * @return string
     */
    protected function getPreviewLinkHtmlId()
    {
        return $this->getHtmlId() . '_preview-link';
    }

    /**
     * @return string
     */
    protected function getPreviewLinkHtml()
    {
        $attributes = new \Magento\Framework\DataObject([
            'id' => $this->getPreviewLinkHtmlId(),
            'href' => '#'
        ]);
        return '<div class=\'preview-link\'><a ' .
            $attributes->serialize() . ' >' . __('View Template') . '</a></div>' . $this->getPreviewLinkJs();
    }

    /**
     * @return string
     */
    protected function getPreviewLinkJs()
    {
        $options = \Zend_Json::encode([
            'url' => $this->url->getUrl('*/*/preview'),
            'template' => '#' . $this->getHtmlId(),
            'status' => '#status_id',
            'toAdmin' => $this->getToAdmin() ? 1: 0
        ]);
        return <<<HTML
    <script>
        require(['jquery', 'awRmaStatusFormPreview'], function($, templatePreview){
            $(document).ready(function() {
                templatePreview({$options}, $('#{$this->getPreviewLinkHtmlId()}'));
            });
        });
    </script>
HTML;
    }
}
