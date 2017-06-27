<?php

namespace IWD\SalesRep\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\AuthorizationException;
use IWD\SalesRep\Model\User as SalesrepUser;

/**
 * Class Data
 * @package IWD\SalesRep\Helper
 */
class Data extends AbstractHelper
{
    const HTTP_REFERRER = 'salesrep';
    const HTTP_REFERRER_KEY = 'referrer';
    const XML_PATH_SHOW_ONLY_ASSIGNED_ORDERS = 'iwd_salesrep/permissions/show_assigned';
    const SESSION_VAR_PARENT_ACCOUNT_ID = 'salesrep_customer_id';

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \IWD\SalesRep\Model\B2BCustomerFactory
     */
    private $salesrepCustomerFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \IWD\SalesRep\Model\ResourceModel\User\CollectionFactory
     */
    private $salesrepCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory
     */
    private $salesrepAssignedCustomerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     * @param \IWD\SalesRep\Model\B2BCustomerFactory $salesrepCustomerFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \IWD\SalesRep\Model\ResourceModel\User\CollectionFactory $salesrepCollectionFactory
     * @param \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $salesrepAssignedCustomerCollectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        \IWD\SalesRep\Model\B2BCustomerFactory $salesrepCustomerFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \IWD\SalesRep\Model\ResourceModel\User\CollectionFactory $salesrepCollectionFactory,
        \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $salesrepAssignedCustomerCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);

        $this->eavConfig = $eavConfig;
        $this->moduleManager = $context->getModuleManager();
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->salesrepCustomerFactory = $salesrepCustomerFactory;
        $this->objectManager = $objectManager;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->salesrepCollectionFactory = $salesrepCollectionFactory;
        $this->salesrepAssignedCustomerCollectionFactory = $salesrepAssignedCustomerCollectionFactory;
        $this->productMetadata = $productMetadata;
        $this->customerSession = $customerSession;
        $this->resource = $resourceConnection;
    }

    /**
     * @return mixed
     * @todo add logic to stores/config or sales rep tab to set customer role, currently hardcoded Wholesale role
     */
    public function getSalesrepCustomerRole()
    {
        try {
            if ($this->isWithB2B()) {
                $b2bHelper = $this->objectManager->create('\IWD\B2B\Helper\Data');
                $groupId = $b2bHelper->getDefaultB2BGroup();
                return $groupId;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getCustomerRoles()
    {
        $res = [];
        $attribute = $this->eavConfig->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'group_id')->getOptions();
        foreach ($attribute as $option) {
            $res[$option->getValue()] = $option->getLabel();
        }
        return $res;
    }

    /**
     * @return bool
     */
    public function isWithB2B()
    {
        return $this->isB2BInstalled() && $this->moduleManager->isEnabled('IWD_B2B');
    }

    /**
     * @return bool
     */
    public function isB2BInstalled()
    {
        return class_exists('\IWD\B2B\Helper\Data');
    }

    /**
     * @param bool $b2bOnly
     * @return array|bool|null
     */
    public function getWebsitesDefaultStores($b2bOnly = false)
    {
        if ($b2bOnly && !$this->isWithB2B()) {
            return null;
        }

        if ($b2bOnly && !$this->scopeConfig->getValue(\Magento\Customer\Model\Config\Share::XML_PATH_CUSTOMER_ACCOUNT_SHARE)) { // global (not per website)
            return true;
        }

        $websites = [];
        $stores = [];
        foreach ($this->storeManager->getStores() as $store) {
            $b2b = $this->scopeConfig->getValue(\IWD\B2B\Helper\Data::XML_PATH_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());
            if (!$b2bOnly || (int)$b2b) {
                $websites[] = $store->getWebsiteId();
            }
        }

        $websites = array_unique($websites);
        foreach ($websites as $w) {
            $g = $this->storeManager->getWebsite($w)->getDefaultGroupId();
            $stores[$w] = $this->storeManager->getGroup($g)->getDefaultStoreId();
        }

        return $stores;
    }

    /**
     * @return array|bool
     */
    public function getEnabledB2BWebsites()
    {
        return $this->getWebsitesDefaultStores(true);
    }

    /**
     * @param $customerId
     * @param $adminUser
     * @param $data
     * @param $salesrepUserId
     */
    public function saveB2BCustomer($customerId, $adminUser, $data, $salesrepUserId)
    {
        $customerModel = $this->customerFactory->create();
        if ($customerId !== null) {
            $customerModel = $customerModel->load($customerId);
        }

        $isNew = $customerModel->isObjectNew();

        $data = $data + [
                'firstname' => $adminUser->getFirstName(),
                'lastname' => $adminUser->getLastName(),
                'email' => $adminUser->getEmail(),
                'group_id' => $this->getSalesrepCustomerRole(),
                'password_hash' => $adminUser->getData('password'),
            ];
        $data['btb_active'] &= $adminUser->getIsActive();
        $customerModel->addData($data);
        $customerModel->save();

        $customerModel->getResource()->saveAttribute($customerModel, 'btb_active');

        if ($isNew) {
            $salesrepCustomer = $this->salesrepCustomerFactory->create();
            $salesrepCustomer->addData([
                'salesrep_id' => $salesrepUserId,
                'customer_id' => $customerModel->getId(),
                'website_id' => $customerModel->getWebsiteId()
            ]);
            $salesrepCustomer->save();
        }
    }

    /**
     * @return array
     */
    public function getSalesrepList()
    {
        $list = [];
        $collection = $this->salesrepCollectionFactory->create();
        $collection->join(
            ['admin_user' => $this->resource->getTableName('admin_user')],
            'main_table.' . SalesrepUser::ADMIN_ID . ' = admin_user.user_id',
            ['name' => new \Zend_Db_Expr('concat(admin_user.firstname, " ", admin_user.lastname)')]
        );

        foreach ($collection->getItems() as $item) {
            $list[$item->getId()] = $item->getData('name');
        }
        return $list;
    }

    /**
     * @param $salesrepId
     * @return \Magento\Framework\DataObject[]
     */
    public function getSalesrepAssignedCustomers($salesrepId)
    {
        $collection = $this->customerCollectionFactory
            ->create()
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('shipping_telephone', 'customer_address/telephone', 'default_shipping', null, 'left');

        $collection->getSelect()
            ->joinInner(
                ['assigned_salesrep' => $this->resource->getTableName(\IWD\SalesRep\Model\ResourceModel\Customer::TABLE_NAME)],
                'e.entity_id = assigned_salesrep.customer_id and assigned_salesrep.salesrep_id = ' . $salesrepId,
                []
            );

        $select = $collection->getSelect();
        $select->joinLeft(
            ['b2b_account_info' => $this->resource->getTableName('iwd_b2b_customer_info')],
            'e.entity_id = b2b_account_info.customer_id',
            []
        );

        $select->joinLeft(['b2b_company' => $this->resource->getTableName('iwd_b2b_company')],
            'b2b_account_info.company_id = b2b_company.company_id',
            ['store_name', 'ssn', 'image']
        );

        $items = $collection->getItems();

        foreach ($items as $key => &$item) {
            // image
            $imagePath = $item->getData('image');
            if ($imagePath) {
                $item->setData('image', $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA, $this->_request->isSecure()) . $imagePath);
            }
            // phone
            if ($item->getData('billing_telephone')) {
                $item->setData('telephone', $item->getData('billing_telephone'));
            } elseif ($item->getData('shipping_telephone')) {
                $item->setData('telephone', $item->getData('shipping_telephone'));
            }
            // name
            $item->setData('name', $item->getData('firstname') . ' ' . $item->getData('lastname'));
        }
        return $items;
    }

    /**
     * check if current customer has permissions to log in as his assigned customers (B2B/Sales Rep feature)
     * @param $initiatorCustomerId
     * @param int|null $destinationId if not null - check on specific customer
     * @return bool
     */
    public function isAllowedToLoginAs($initiatorCustomerId, $destinationId = null)
    {
        $initiator = $this->customerFactory->create()->load($initiatorCustomerId);
        $salesrepId = $initiator->getData(\IWD\SalesRep\Model\Plugin\Customer\ResourceModel\Customer::KEY_SALESREP_ACCOUNT_ID);
        if (!$salesrepId) {
            return false;
        }

        $collection = $this->salesrepAssignedCustomerCollectionFactory->create()
            ->addFieldToFilter('salesrep_id', $salesrepId);
        if ($destinationId) {
            $collection->addFieldToFilter('customer_id', $destinationId);
        }

        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    public function loginAs($destinationId)
    {
        $customer = $this->customerFactory->create()->load($destinationId);

        if ($customer->isEmpty() || $customer->isObjectNew()) {
            throw new AuthorizationException(__('No such customer'));
        }

        $this->customerSession->logout();
        $res = $this->customerSession->loginById($customer->getId());
        $this->customerSession->regenerateId();

        return $res;
    }

    public function loginAsAssignedCustomer($destinationCustomerId)
    {
        $initiatorCustomerId = $this->customerSession->getCustomerId();
        try {
            if ($this->loginAs($destinationCustomerId)) {
                $this->customerSession->setData(self::SESSION_VAR_PARENT_ACCOUNT_ID, $initiatorCustomerId);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function returnToParentAccount()
    {
        $destinationCustomerId = $this->customerSession->getData(self::SESSION_VAR_PARENT_ACCOUNT_ID);
        if (!$destinationCustomerId) {
            return null;
        }

        try {
            if ($this->loginAs($destinationCustomerId)) {
                $this->customerSession->unsSalesrepCustomerId();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return $this|bool
     * @throws \IWD\SalesRep\Exceptions\MissingParentAccountException
     */
    public function getParentAccount()
    {
        if ($this->customerSession->getData(self::SESSION_VAR_PARENT_ACCOUNT_ID)) {
            $parentCustomer = $this->customerFactory->create()->load($this->customerSession->getData(self::SESSION_VAR_PARENT_ACCOUNT_ID));

            if ($parentCustomer->isEmpty() || $parentCustomer->isObjectNew()) {
                throw new \IWD\SalesRep\Exceptions\MissingParentAccountException();
            }

            return $parentCustomer;
        }

        return false;
    }

    /**
     * @param $customer_id
     * @return $this
     */
    public function getCustomerInfo($customer_id)
    {
        return $this->customerFactory->create()->load($customer_id);
    }

    /**
     * @return bool
     */
    public function isEE()
    {
        return $this->getMagentoEdition() == 'Enterprise';
    }

    /**
     * @return string
     */
    public function getMagentoEdition()
    {
        return $this->productMetadata->getEdition();
    }
}
