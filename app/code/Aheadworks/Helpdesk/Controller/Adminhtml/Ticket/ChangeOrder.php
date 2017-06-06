<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Ticket;

/**
 * Class ChangeOrder
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
 */
class ChangeOrder extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Aheadworks_Helpdesk::tickets';

    /**
     * Order model factory
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Order resource model
     *
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    protected $orderResourceModel;

    /**
     * Order items renderer
     *
     * @var \Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\General\Items
     */
    protected $itemsRenderer;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResource
     * @param \Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\General\Items $itemsRenderer
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResource,
        \Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\General\Items $itemsRenderer
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderResourceModel = $orderResource;
        $this->itemsRenderer = $itemsRenderer;
        parent::__construct($context);
    }

    /**
     * Prepare new order data
     *
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $orderId = $this->getRequest()->getParam('order_id', null);

        $orderModel = $this->orderFactory->create();
        $this->orderResourceModel->load($orderModel, $orderId);
        if ($orderModel->getId()) {
            $result['status'] = $orderModel->getStatusLabel();

            $url = $this->_url->getUrl('sales/order/view', ['order_id' => $orderModel->getId()]);
            $result['external_link'] = $url;

            $result['created_at'] = $orderModel->getCreatedAtFormatted(\IntlDateFormatter::SHORT);
            $result['order_items'] = $this->itemsRenderer->getBlockHtml($orderModel);
            $result['success'] = true;
        } else {
            $result['success'] = false;
        }

        return $resultJson->setData($result);
    }
}
