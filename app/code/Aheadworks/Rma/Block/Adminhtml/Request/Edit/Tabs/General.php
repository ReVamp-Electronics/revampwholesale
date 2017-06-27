<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Rma\Block\Adminhtml\Request\Edit\Tabs;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class General
 * @package Aheadworks\Rma\Block\Adminhtml\Request\Edit\Tabs
 * @method \Aheadworks\Rma\Model\Request getRmaRequest()
 */
class General extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Aheadworks\Rma\Model\Source\Request\Status
     */
    protected $statusSource;

    /**
     * @var \Aheadworks\Rma\Model\CustomFieldFactory
     */
    protected $customFieldFactory;

    /**
     * @var string
     */
    protected $_template = 'request/edit/tabs/general.phtml';

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Aheadworks\Rma\Model\Source\Request\Status $statusSource
     * @param \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Aheadworks\Rma\Model\Source\Request\Status $statusSource,
        \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->statusSource = $statusSource;
        $this->customFieldFactory = $customFieldFactory;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->setRmaRequest($this->coreRegistry->registry('aw_rma_request'));
        parent::_construct();
    }

    //todo move this to request model
    public function getStatusLabel()
    {
        return $this->statusSource->getOptionLabelByValue($this->getRmaRequest()->getStatusId());
    }

    public function getResolution()
    {
        $resolutionCustomField = $this->customFieldFactory->create()->load('Resolution', 'name');
        $optionValue = $this->getRmaRequest()->getCustomFields($resolutionCustomField->getId());
        return $resolutionCustomField->getOptionLabelByValue($optionValue);
    }

    public function getItemReason(\Aheadworks\Rma\Model\RequestItem $item)
    {
        $reasonCustomField = $this->customFieldFactory->create()->load('Reason', 'name');
        $optionValue = $item->getCustomFields($reasonCustomField->getId());
        return $reasonCustomField->getOptionLabelByValue($optionValue);
    }

    public function getRequestCreatedAt()
    {
        return $this->formatDate($this->getRmaRequest()->getCreatedAt(), \IntlDateFormatter::MEDIUM);
    }

    public function getProductLink($productId)
    {
        return $this->getUrl('catalog/product/edit', ['id' => $productId]);
    }

    public function formatPrice($price)
    {
        return $this->priceCurrency->format($price);
    }
}
