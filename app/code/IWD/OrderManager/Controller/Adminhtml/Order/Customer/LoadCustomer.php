<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Customer;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;
use Magento\Customer\Model\Customer;
use Magento\Sales\Model\Order;

class LoadCustomer extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_customer';

    /**
     * @var Customer
     */
    protected $_customer;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @param Context $context
     * @param Customer $customer
     * @param Order $order
     */
    public function __construct(
        Context $context,
        Customer $customer,
        Order $order
    ) {
        parent::__construct($context);

        $this->_customer = $customer;
        $this->_order = $order;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $response = $this->prepareResponse();
        } catch (\Exception $e) {
            $response = [
                'error' => $e->getMessage(),
                'status' => false
            ];
        }

        $updateResult = new DataObject($response);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($updateResult);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function prepareResponse()
    {
        $email = $this->getCustomerEmail();
        $websiteId = $this->getOrder()->getStore()->getWebsiteId();

        $this->_customer->setWebsiteId($websiteId);
        $customer = $this->_customer->loadByEmail($email);

        if (!$customer->getId()) {
            throw new LocalizedException(__('Can not load customer with email %1', $email));
        }

        $params = [
            'customer_group_id' => 'group_id',
            'customer_dob' => 'dob',
            'customer_email' => 'email',
            'customer_firstname' => 'firstname',
            'customer_lastname' => 'lastname',
            'customer_middlename' => 'middlename',
            'customer_prefix' => 'prefix',
            'customer_suffix' => 'suffix',
            'customer_taxvat' => 'taxvat',
            'customer_gender' => 'gender',
            'customer_id' => 'entity_id'
        ];

        foreach ($params as $id => $param) {
            $params[$id] = $customer->getData($param);
        }

        return $params;
    }

    /**
     * @return Order
     * @throws \Exception
     */
    protected function getOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $this->_order->load($id);
        if (!$this->_order->getEntityId()) {
            throw new LocalizedException(__('Can not load order'));
        }

        return $this->_order;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getCustomerEmail()
    {
        $customerInfo = $this->getRequest()->getParam('customer_info', []);
        if (!isset($customerInfo['customer_email']) || empty($customerInfo['customer_email'])) {
            throw new LocalizedException(__('Email is empty'));
        }

        return $customerInfo['customer_email'];
    }
}
