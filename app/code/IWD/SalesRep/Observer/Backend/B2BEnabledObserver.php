<?php

namespace IWD\SalesRep\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Magento\User\Model\User;
use Magento\Customer\Model\CustomerFactory;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use \IWD\SalesRep\Model\User as SalesrepUser;

class B2BEnabledObserver implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var UserCollectionFactory
     */
    protected $_userCollectionFactory;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory
     */
    protected $_salesrepCustomerCollectionFactory;

    /**
     * @var \IWD\SalesRep\Helper\Data
     */
    protected $_salesrepHelper;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    public function __construct(
        UserCollectionFactory $userCollectionFactory,
        CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory $salesrepCustomerCollectionFactory,
        \IWD\SalesRep\Helper\Data $salesrepHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->_userCollectionFactory = $userCollectionFactory;
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_salesrepCustomerCollectionFactory = $salesrepCustomerCollectionFactory;
        $this->_salesrepHelper = $salesrepHelper;
        $this->moduleManager = $moduleManager;
        $this->_resource = $resourceConnection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $configData = $observer->getData('data_object');
        $currentScope = $configData->getData('scope');
        $scopeId = $configData->getData('scope_id');

        if (!$this->_scopeConfig->getValue(\Magento\Customer\Model\Config\Share::XML_PATH_CUSTOMER_ACCOUNT_SHARE)) {
            return;
        }

        if ($this->moduleManager->isEnabled('IWD_B2B') && $configData->getData('path') == \IWD\B2B\Helper\Data::XML_PATH_ENABLE) {
            $value = $this->_scopeConfig->getValue($configData->getData('path'), $currentScope, $scopeId);

            // is enabled
            if ($value) {
                if ($currentScope == 'default') {
                    // enabled for all stores/websites
                    // run throw all websites and create b2b customers, if needed
                    $websites = $this->_salesrepHelper->getWebsitesDefaultStores();
                } else {
                    // create b2b customers, if needed, for current website
                    if ($currentScope == 'stores') {
                        $websiteId = $this->_storeManager->getStore($scopeId)->getWebsiteId();
                        $storeId = $scopeId;
                    } else {// website
                        $enabledB2BWebsites = $this->_salesrepHelper->getEnabledB2BWebsites();
                        $websiteId = $scopeId;
                        $storeId = $enabledB2BWebsites[$websiteId];
                    }
                    $websites = [$websiteId => $storeId];
                }

                // gather all sales rep/admin users
                /**
                 * @var $salesRepAdmins \Magento\User\Model\ResourceModel\User\Collection
                 */
                $salesRepAdmins = $this->_userCollectionFactory->create();
                $salesrepUserTable = $this->_resource->getTableName(\IWD\SalesRep\Model\ResourceModel\User::TABLE_NAME);
                $salesRepAdmins->join(['salesrep_user' => $salesrepUserTable],
                    'main_table.user_id = salesrep_user.' . SalesrepUser::ADMIN_ID,
                    [
                        'salesrep_id' => 'salesrep_user.' .SalesrepUser::SALESREP_ID,
                    ]
                );

                $salesReps = [];
                foreach ($salesRepAdmins->getItems() as $adminUser) {
                    $salesReps[$adminUser->getData('salesrep_id')] = $adminUser;
                }

                $existingB2BCustomers = $this->_salesrepCustomerCollectionFactory->create()
                    ->addFieldToFilter('website_id', ['in' => array_keys($websites)]);

                $customersInWebsite = [];
                foreach ($existingB2BCustomers as $b2bCustomer) {
                    if (!isset($customersInWebsite[$b2bCustomer->getWebsiteId()])) {
                        $customersInWebsite[$b2bCustomer->getWebsiteId()] = [];
                    }
                    $customersInWebsite[$b2bCustomer->getWebsiteId()][] = $b2bCustomer->getData('salesrep_id');
                }

                foreach ($salesReps as $salesrepId => $adminUser) {
                    foreach ($websites as $websiteId => $storeId) {
                        // if there is no b2b customer of $salesrepId in this website
                        if (!isset($customersInWebsite[$websiteId]) ||
                            !in_array($salesrepId, $customersInWebsite[$websiteId])
                        ) {
                            // todo check
                            // create it
                            $this->_salesrepHelper->saveB2BCustomer(null, $adminUser, [
                                'website_id' => $websiteId,
                                'store_id' => $websites[$websiteId],
                                'btb_active' => $adminUser->getData('is_active')
                            ], $salesrepId);
                        }
                    }
                }
            }
        }
    }
}
