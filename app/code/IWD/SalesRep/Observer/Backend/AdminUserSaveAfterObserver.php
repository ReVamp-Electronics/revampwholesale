<?php

namespace IWD\SalesRep\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use IWD\SalesRep\Model\User as SalesrepUser;
use IWD\SalesRep\Helper\Data as SalesrepHelper;

/**
 * Class AdminUserSaveAfterObserver
 * @package IWD\SalesRep\Observer\Backend
 */
class AdminUserSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \IWD\SalesRep\Model\UserFactory
     */
    private $salesrepUserFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var SalesrepHelper
     */
    private $salesrepHelper;

    /**
     * @var \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory
     */
    private $salesrepCustomerCollectionFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    /**
     * AdminUserSaveAfterObserver constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param Registry $registry
     * @param \IWD\SalesRep\Model\UserFactory $salesrepUserFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param SalesrepHelper $salesrepHelper
     * @param \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory $salesrepCustomerCollectionFactory
     * @param \Magento\Backend\Model\Session $session
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Registry $registry,
        \IWD\SalesRep\Model\UserFactory $salesrepUserFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\User\Model\UserFactory $userFactory,
        SalesrepHelper $salesrepHelper,
        \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory $salesrepCustomerCollectionFactory,
        \Magento\Backend\Model\Session $session
    ) {
        $this->request = $context->getRequest();
        $this->salesrepUserFactory = $salesrepUserFactory;
        $this->customerFactory = $customerFactory;
        $this->userFactory = $userFactory;
        $this->salesrepHelper = $salesrepHelper;
        $this->salesrepCustomerCollectionFactory = $salesrepCustomerCollectionFactory;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $adminUser \Magento\User\Model\User
         */
        $adminUser = $observer->getData('data_object');

        // save sales rep
        $salesrepUserModel = $this->salesrepUserFactory->create()->load($adminUser->getId(), SalesrepUser::ADMIN_ID);

        $salesrepData = [SalesrepUser::ADMIN_ID => $adminUser->getId()];

        $accountData = $this->request->getParam('salesrep');
        if (!empty($accountData)) {
            $salesrepData = array_merge($salesrepData, $accountData);
        }
        $salesrepUserModel->addData($salesrepData);
        if (!$salesrepUserModel->isObjectNew() || $salesrepUserModel->getEnabled()) {
            $salesrepUserModel->save();
        } else {
            // if there is no customer and this admin user is not Sales Rep - skip creating b2b customer
            return;
        }
        // END save sales rep

        // save related customer
        // only for B2B
        if (!$this->salesrepHelper->isWithB2B()) {
            return;
        }

        $pureUserModel = $this->userFactory->create()->load($adminUser->getId());

        $websites = $this->salesrepHelper->getEnabledB2BWebsites();
        if ($websites === true) {
            $websites = [0];
        }

        $existingCustomersCollection = $this->salesrepCustomerCollectionFactory->create()
            ->addFieldToFilter('salesrep_id', $salesrepUserModel->getId());

        $salesrepCustomers = [];
        foreach ($existingCustomersCollection as $salesrepCustomer) {
            $salesrepCustomers[$salesrepCustomer->getWebsiteId()] = $salesrepCustomer;
        }

        $this->session->setIsSROperation('yes');
        
        foreach ($websites as $websiteId => $defaultStoreId) {
            $customerId = null;
            if (isset($salesrepCustomers[$websiteId])) {
                $salesrepCustomer = $salesrepCustomers[$websiteId];
                $customerId = $salesrepCustomer->getCustomerId();
                $salesrepCustomers[$websiteId]->setData('_b2b_saved', true);
            }
            $_data = [
                'btb_active' => $salesrepUserModel->getEnabled(),
            ];
            if ($websiteId && $defaultStoreId) {
                $_data = $_data + [
                        'website_id' => $websiteId,
                        'store_id' => $defaultStoreId,
                    ];
            }
            $this->salesrepHelper->saveB2BCustomer($customerId, $pureUserModel, $_data, $salesrepUserModel->getId());
        }
        
        $this->session->setIsSROperation(null);

        // save those b2b accounts, that are existing, but for stores with disabled b2b
        // (test case - such b2b accounts were created when B2B was enabled for this store)
        foreach ($salesrepCustomers as $websiteId => $salesrepCustomer) {
            if ($salesrepCustomer->getData('_b2b_saved') === null) {
                $customerId = $salesrepCustomer->getCustomerId();
                $customerModel = $this->customerFactory->create()->load($customerId);
                $customerModel->setData('btb_active', $salesrepUserModel->getEnabled());
                $customerModel->getResource()->saveAttribute($customerModel, 'btb_active');
                $salesrepCustomers[$websiteId]->setData('_b2b_saved', true);
            }
        }
        // END save related customer
    }
}
