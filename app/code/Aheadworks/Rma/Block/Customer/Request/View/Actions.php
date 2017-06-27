<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Customer\Request\View;

/**
 * Class Actions
 * @package Aheadworks\Rma\Block\Customer\Request\View
 */
class Actions extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_CONFIRM_SHIPPING_POPUP_TEXT = 'aw_rma/general/confirm_shipping_popup_text';

    /**
     * @var string
     */
    protected $_template = 'customer/request/view/actions.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Aheadworks\Rma\Helper\Status
     */
    protected $statusHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Aheadworks\Rma\Helper\Status $statusHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Aheadworks\Rma\Helper\Status $statusHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->statusHelper = $statusHelper;
    }

    /**
     * @return \Aheadworks\Rma\Model\Request
     */
    public function getRequestModel()
    {
        return $this->coreRegistry->registry('aw_rma_request');
    }

    /**
     * @return bool
     */
    public function canCancel()
    {
        return $this->statusHelper->isAvailableForCustomerCancel(
            $this->getRequestModel()->getStatusId()
        );
    }

    /**
     * @return bool
     */
    public function canPrintLabel()
    {
        return $this->statusHelper->isAvailableForPrintLabel(
            $this->getRequestModel()->getStatusId()
        );
    }

    /**
     * @return bool
     */
    public function canConfirmShipping()
    {
        return $this->statusHelper->isAvailableForConfirmShipping(
            $this->getRequestModel()->getStatusId()
        );
    }

    /**
     * @return string
     */
    public function getConfirmShippingPopupText()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_CONFIRM_SHIPPING_POPUP_TEXT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getUrl('*/*/cancel', ['id' => $this->getRequestModel()->getId()]);
    }

    /**
     * @return string
     */
    public function getPrintLabelUrl()
    {
        return $this->getUrl('*/*/printLabel', ['id' => $this->getRequestModel()->getId()]);
    }

    /**
     * @return string
     */
    public function getConfirmShipping()
    {
        return $this->getUrl('*/*/confirmShipping', ['id' => $this->getRequestModel()->getId()]);
    }
}
