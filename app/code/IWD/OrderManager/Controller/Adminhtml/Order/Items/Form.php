<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Items;

use IWD\OrderManager\Model\Order\Order;
use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

class Form extends AbstractAction
{
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
     * {@inheritdoc}
     */
    protected function getResultHtml()
    {
        $formContainer = $this->resultPageFactory->create()->getLayout()
            ->getBlock('iwdordermamager_order_items_form_container');

        if (empty($formContainer)) {
            throw new LocalizedException(__('Can not load block'));
        }

        $order = $this->getOrder();
        $order->syncQuote();

        $formContainer->setOrder($order);

        return $formContainer->toHtml();
    }

    /**
     * @return Order
     * @throws \Exception
     */
    protected function getOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $this->order->load($id);
        if (!$this->order->getEntityId()) {
            throw new \Exception('Can not load order');
        }
        return $this->order;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return
            $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_items_edit') ||
            $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_items_delete') ||
            $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_items_add');
    }
}
