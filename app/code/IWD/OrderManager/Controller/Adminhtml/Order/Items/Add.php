<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Items;

use IWD\OrderManager\Model\Order\Converter;
use IWD\OrderManager\Model\Order\Order;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Add
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Items
 */
class Add extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_items_add';

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Converter
     */
    private $orderConverter;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var DataObject
     */
    private $dataObject;

    /**
     * Add constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Converter $orderConverter
     * @param Order $order
     * @param DataObject $dataObject
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Converter $orderConverter,
        Order $order,
        DataObject $dataObject
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->orderConverter = $orderConverter;
        $this->order = $order;
        $this->dataObject = $dataObject;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $response = [
                'result' => $this->prepareResultHtml(),
                'status' => true
            ];
        } catch (\Exception $e) {
            $response = [
                'error' => $e->getMessage(),
                'status' => false
            ];
        }

        $updateResult = $this->dataObject->addData($response);
        $this->_session->setIwdOmAddedItemsResult($updateResult);

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('iwdordermanager/order_items/addResult');
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function prepareResultHtml()
    {
        $resultPage = $this->resultPageFactory->create();

        /**
         * @var $formContainer \IWD\OrderManager\Block\Adminhtml\Order\Items\NewItem\Form
         */
        $formContainer = $resultPage->getLayout()->getBlock('iwdordermamager_order_items_form_container');
        if (empty($formContainer)) {
            throw new LocalizedException(__('Can not load block'));
        }

        $order = $this->getOrder();
        $orderItems = $this->getNewOrderItems();
        $errors = $this->orderConverter->getErrors();

        $formContainer->setOrder($order);
        $formContainer->setNewOrderItems($orderItems);
        $formContainer->setErrors($errors);

        return $formContainer->toHtml();
    }

    /**
     * @return Order
     * @throws \Exception
     */
    private function getOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $this->order->load($id);
        if (!$this->order->getEntityId()) {
            throw new LocalizedException(__('Can not load order'));
        }
        return $this->order;
    }

    /**
     * @return \IWD\OrderManager\Model\Order\Item[]
     */
    private function getNewOrderItems()
    {
        $items = $this->getRequest()->getParam('item', []);
        $order = $this->getOrder();

        return $this->orderConverter->createNewOrderItems($items, $order);
    }
}
