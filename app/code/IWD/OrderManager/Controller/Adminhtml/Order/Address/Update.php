<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Address;

use IWD\OrderManager\Helper\Data;
use IWD\OrderManager\Controller\Adminhtml\Order\Additional\AbstractAction;
use IWD\OrderManager\Model\Order\Address;
use IWD\OrderManager\Model\Order\Order;
use IWD\OrderManager\Model\Quote\Quote;
use IWD\OrderManager\Model\Order\Shipping;
use IWD\OrderManager\Model\Order\Payment;
use IWD\OrderManager\Model\Log\Logger;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Update
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Address
 */
class Update extends AbstractAction
{
    /**
     * @var \IWD\OrderManager\Model\Order\Address
     */
    protected $_address;

    /**
     * Update constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param Quote $quote
     * @param Order $order
     * @param Shipping $shipping
     * @param Payment $payment
     * @param Address $address
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        Quote $quote,
        Order $order,
        Shipping $shipping,
        Payment $payment,
        Address $address
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $helper,
            $scopeConfig,
            $quote,
            $order,
            $shipping,
            $payment
        );
        $this->_address = $address;
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function update()
    {
        $addressId = $this->getAddressId();
        $addressData = $this->getAddressData();

        $this->_address->loadAddress($addressId);
        $this->_address->updateAddress($addressData);
        $this->updateUserAddress($addressData);
    }

    /**
     * @param string[] $addressData
     * @return void
     */
    protected function updateUserAddress($addressData)
    {
        $applyForCustomer = $this->getRequest()
            ->getParam('apply_for_customer', false);

        if (!empty($applyForCustomer)) {
            $this->_address->updateCustomerAddress($addressData);
            Logger::getInstance()->addMessage(
                'Customer address information was updated based on order address information.'
            );
        }
    }

    /**
     * @return string
     */
    protected function prepareResponse()
    {
        return ['result' => 'reload'];
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getAddressId()
    {
        $id = $this->getRequest()->getParam('address_id', null);
        if (empty($id)) {
            throw new LocalizedException(__('Empty param address_id'));
        }

        return $id;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getAddressData()
    {
        $data = $this->getRequest()->getParams();

        if (isset($data['billing_address'])) {
            return $data['billing_address'];
        }

        if (isset($data['shipping_address'])) {
            return $data['shipping_address'];
        }

        throw new LocalizedException(__('Have not address data information'));
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_address_billing')
        || $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_address_shipping');
    }
}
