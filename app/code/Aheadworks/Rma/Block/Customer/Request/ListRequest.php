<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Customer\Request;

/**
 * Class ListRequest
 * @package Aheadworks\Rma\Block\Customer\Request
 */
class ListRequest extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'customer/request/list.phtml';

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\Request\CollectionFactory
     */
    protected $requestCollectionFactory;

    /**
     * @var null|\Aheadworks\Rma\Model\ResourceModel\Request\Collection
     */
    protected $requestCollection = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Aheadworks\Rma\Helper\Order
     */
    protected $orderHelper;

    /**
     * @var array
     */
    protected $products = [];

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Aheadworks\Rma\Model\ResourceModel\Request\CollectionFactory $requestCollectionFactory
     * @param \Aheadworks\Rma\Helper\Order $orderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Aheadworks\Rma\Model\ResourceModel\Request\CollectionFactory $requestCollectionFactory,
        \Aheadworks\Rma\Helper\Order $orderHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->productFactory = $productFactory;
        $this->requestCollectionFactory = $requestCollectionFactory;
        $this->orderHelper = $orderHelper;
    }

    /**
     * @return \Aheadworks\Rma\Model\ResourceModel\Request\Collection|bool|null
     */
    public function getRequestCollection()
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if ($this->requestCollection === null) {
            $this->requestCollection = $this->requestCollectionFactory->create()
                ->addCustomerFilter($customerId)
                ->joinStatusAttributeValues(['frontend_label'])
                ->joinOrders()
                ->addOrder('created_at')
            ;
        }
        return $this->requestCollection;
    }

    /**
     * @param int $productId
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProduct($productId)
    {
        if (!isset($this->products[$productId])) {
            $this->products[$productId] = $this->productFactory->create()->load($productId);
        }
        return $this->products[$productId];
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getRequestCollection()) {
            /** @var \Magento\Theme\Block\Html\Pager $pager */
            $pager = $this->getChildBlock('pager');
            if ($pager) {
                $pager->setCollection($this->getRequestCollection());
                $this->getRequestCollection()->load();
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getRequestCollection() ? $this->getChildHtml('pager') : '';
    }

    /**
     * @param $productId
     * @return bool
     */
    public function isProductExists($productId)
    {
        return (bool)$this->getProduct($productId)->getId();
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getOrderViewUrl($orderId)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * @param $requestItem
     * @return string
     */
    public function getProductViewUrl($requestItem)
    {
        $product = $this->getProduct($requestItem->getProductId());
        $parentProductId = $requestItem->getParentProductId();
        if ($parentProductId) {
            $parentProduct = $this->getProduct($parentProductId);
            if (in_array($parentProduct->getTypeId(), $this->orderHelper->getNotReturnedOrderItemProductTypes())) {
                return $parentProduct->getProductUrl();
            }
        }
        return $product->getProductUrl();
    }

    /**
     * @param int $requestId
     * @return string
     */
    public function getRequestViewUrl($requestId)
    {
        return $this->getUrl('*/*/view', ['id' => $requestId]);
    }
}
