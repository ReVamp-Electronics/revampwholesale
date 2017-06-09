<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Info;

use IWD\OrderManager\Model\Order\Order;
use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;

class Form extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_info';

    /**
     * @var Order
     */
    private $order;

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
        $this->order = $order;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getResultHtml()
    {
        $resultPage = $this->resultPageFactory->create();

        /** @var \IWD\OrderManager\Block\Adminhtml\Order\Info\Form $infoFormContainer */
        $infoFormContainer = $resultPage->getLayout()->getBlock('iwdordermamager_order_info_form');
        if (empty($infoFormContainer)) {
            throw new LocalizedException(__('Can not load block'));
        }

        $order = $this->getOrder();
        $infoFormContainer->setOrder($order);

        return $infoFormContainer->toHtml();
    }

    /**
     * @return Order
     * @throws \Exception
     */
    protected function getOrder()
    {
        $orderId = $this->getOrderId();
        return $this->order->load($orderId);
    }
}
