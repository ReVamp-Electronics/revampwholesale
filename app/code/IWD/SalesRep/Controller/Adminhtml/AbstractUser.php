<?php

namespace IWD\SalesRep\Controller\Adminhtml;

/**
 * Class AbstractUser
 * @package IWD\SalesRep\Controller\Adminhtml
 */
abstract class AbstractUser extends \Magento\Backend\App\AbstractAction
{
    /**
     * @var \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $attachedCustomerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * AbstractUser constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $attachedCustomerCollection
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $attachedCustomerCollection
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->customerFactory = $customerFactory;
        $this->attachedCustomerCollectionFactory = $attachedCustomerCollection;
    }
}
