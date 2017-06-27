<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Customer\Request\NewRequest\Step\SelectOrder\Items;

/**
 * Class Renderer
 * @package Aheadworks\Rma\Block\Customer\Request\NewRequest\Step\SelectOrder\Items
 */
class Renderer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    protected $itemRenderersTemplates = [
        'default' => 'customer/request/newrequest/step/selectorder/items/renderer/default.phtml',
        'bundle' => 'customer/request/newrequest/step/selectorder/items/renderer/bundle.phtml',
        'configurable' => 'customer/request/newrequest/step/selectorder/items/renderer/configurable.phtml'
    ];

    /**
     * @var \Magento\Sales\Model\Order\Item|null
     */
    protected $item = null;

    /**
     * @var \Magento\Sales\Model\Order\Item|null
     */
    protected $parentItem = null;

    /**
     * @var \Aheadworks\Rma\Helper\Order
     */
    protected $orderHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Aheadworks\Rma\Helper\Order $orderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Aheadworks\Rma\Helper\Order $orderHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderHelper = $orderHelper;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return $this
     */
    public function setItem(\Magento\Sales\Model\Order\Item $item)
    {
        $this->item = $item;
        $this->setTemplate($this->getItemRendererTemplate($item));
        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return $this
     */
    public function setParentItem(\Magento\Sales\Model\Order\Item $item)
    {
        $this->parentItem = $item;
        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Order\Item|null
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return bool
     */
    public function canRender()
    {
        $item = $this->getItem();
        if ($item === null) {
            return false;
        }
        $parentItem = $item->getParentItem();
        if ($parentItem) {
            return (
                in_array($parentItem->getProductType(), $this->orderHelper->getNotReturnedOrderItemProductTypes()) &&
                ($this->parentItem !== null && $parentItem->getId() == $this->parentItem->getId())
            );
        }
        return true;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return string
     */
    public function getItemRendererTemplate(\Magento\Sales\Model\Order\Item $orderItem)
    {
        $rendererType = isset($this->itemRenderersTemplates[$orderItem->getProductType()]) ?
            $orderItem->getProductType() :
            'default'
        ;
        return $this->itemRenderersTemplates[$rendererType];
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return \Aheadworks\Rma\Block\Customer\Request\NewRequest\Step\SelectOrder\Items\Renderer
     */
    public function getItemRenderer(\Magento\Sales\Model\Order\Item $item)
    {
        return $this->getLayout()
            ->createBlock('Aheadworks\Rma\Block\Customer\Request\NewRequest\Step\SelectOrder\Items\Renderer')
            ->setItem($item)
            ->setParentItem($this->item)
            ;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return int
     */
    public function getItemMaxCount(\Magento\Sales\Model\Order\Item $item)
    {
        return $this->orderHelper->getItemMaxCount($item);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function isAllowedForOrder(\Magento\Sales\Model\Order $order)
    {
        return $this->orderHelper->isAllowedForOrder($order);
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return \Aheadworks\Rma\Model\ResourceModel\Request\Collection
     */
    public function getRequestsForItem(\Magento\Sales\Model\Order\Item $item)
    {
        return $this->orderHelper->getAllRequestsForOrderItem($item);
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return string
     */
    public function getProductViewUrl(\Magento\Sales\Model\Order\Item $orderItem)
    {
        $product = $orderItem->getProduct();
        $parentItem = $orderItem->getParentItem();
        if ($parentItem) {
            $parentProduct = $parentItem->getProduct();
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
