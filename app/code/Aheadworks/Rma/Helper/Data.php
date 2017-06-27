<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Helper;

/**
 * Class Data
 * @package Aheadworks\Rma\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $urlBuilderBackend;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\Url $urlBuilderBackend
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Url $urlBuilderBackend
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->urlBuilderBackend = $urlBuilderBackend;
        parent::__construct($context);
    }

    /**
     * @param $requestModel \Aheadworks\Rma\Model\Request
     * @return string
     */
    public function getRmaLink($requestModel)
    {
        $this->urlBuilder->setScope($requestModel->getStoreId());
        if ($requestModel->getCustomer()) {
            $rmaLink = $this->urlBuilder->getUrl(
                'aw_rma/customer/view',
                ['id' => $requestModel->getId(), '_nosid' => true]
            );
        } else {
            $rmaLink = $this->urlBuilder->getUrl(
                'aw_rma/guest/view',
                ['id' => $requestModel->getExternalLink(), '_nosid' => true]
            );
        }
        return $rmaLink;
    }

    /**
     * @param $requestModel
     * @return string
     */
    public function getAdminRmaLink($requestModel)
    {
        return $this->urlBuilderBackend->getUrl('aw_rma_admin/rma/edit', ['id' => $requestModel->getId()]);
    }

    /**
     * @param $order
     * @return string
     */
    public function getAdminOrderLink($order)
    {
        return $this->urlBuilderBackend->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }
}