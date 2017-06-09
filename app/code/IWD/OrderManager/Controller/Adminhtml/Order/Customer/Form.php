<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Customer;

use IWD\OrderManager\Model\Order\Order;
use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Form
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Customer
 */
class Form extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_customer';

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param Order $order
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        Order $order
    ) {
        parent::__construct($context, $resultPageFactory, $helper);
        $this->_order = $order;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getResultHtml()
    {
        $resultPage = $this->resultPageFactory->create();

        $customerFormContainer = $resultPage->getLayout()
            ->getBlock('iwdordermamager_order_customer_form');
        if (empty($customerFormContainer)) {
            throw new LocalizedException(__('Can not load block'));
        }

        $order = $this->getOrder();
        $customerFormContainer->setOrder($order);

        return $customerFormContainer->toHtml();
    }

    /**
     * @return Order
     * @throws \Exception
     */
    protected function getOrder()
    {
        $orderId = $this->getOrderId();
        return $this->_order->load($orderId);
    }
}
