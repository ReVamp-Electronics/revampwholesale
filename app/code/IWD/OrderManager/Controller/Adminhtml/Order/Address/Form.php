<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Address;

use IWD\OrderManager\Model\Order\Address;
use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

/**
 * Class Form
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Address
 */
class Form extends AbstractAction
{
    /**
     * @var \IWD\OrderManager\Model\Order\Address
     */
    private $address;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param Address $address
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        Address $address,
        Registry $coreRegistry
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $helper,
            AbstractAction::ACTION_GET_FORM
        );
        $this->address = $address;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * {@inheritdoc}
     */
    protected function getResultHtml()
    {
        $this->prepareAddress();

        $resultPage = $this->resultPageFactory->create();

        /**
         * @var \IWD\OrderManager\Block\Adminhtml\Order\Address\Form $addressFormContainer
         */
        $addressFormContainer = $resultPage->getLayout()->getBlock('iwdordermamager_order_address_form_container');
        if (empty($addressFormContainer)) {
            throw new LocalizedException(__('Can not load block'));
        }

        $addressType = $this->getAddressType();

        return $addressFormContainer->setAddressType($addressType)->toHtml();
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getAddressType()
    {
        $addressType = $this->getRequest()->getParam('address_type', null);
        if (empty($addressType)) {
            throw new LocalizedException(__('Address type is empty'));
        }
        return $addressType;
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function prepareAddress()
    {
        $addressId = $this->getRequest()->getParam('address_id', 0);
        $address = $this->address->loadAddress($addressId);
        $this->coreRegistry->register('order_address', $address);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return
            $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_address_billing') ||
            $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_address_shipping');
    }
}
