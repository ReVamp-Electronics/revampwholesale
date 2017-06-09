<?php

namespace IWD\SalesRep\Model\Preference\Customer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\ValidationResultsInterfaceFactory;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Registry;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use IWD\SalesRep\Model\User as SalesrepUser;
use IWD\SalesRep\Model\B2BCustomer as SalesrepCustomer;
use Psr\Log\LoggerInterface as PsrLogger;

class AccountManagement extends \Magento\Customer\Model\AccountManagement
{
    /**
     * @var \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory
     */
    private $salesrepCustomerCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $connection;

    public function __construct(
        CustomerFactory $customerFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        Validator $validator,
        ValidationResultsInterfaceFactory $validationResultsDataFactory,
        AddressRepositoryInterface $addressRepository,
        CustomerMetadataInterface $customerMetadataService,
        CustomerRegistry $customerRegistry,
        PsrLogger $logger,
        Encryptor $encryptor,
        ConfigShare $configShare,
        StringHelper $stringHelper,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        DataObjectProcessor $dataProcessor,
        Registry $registry,
        CustomerViewHelper $customerViewHelper,
        DateTime $dateTime,
        CustomerModel $customerModel,
        ObjectFactory $objectFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Framework\App\ResourceConnection $connection,
        \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory $salesrepCustomerCollectionFactory
    ) {
        parent::__construct(
            $customerFactory,
            $eventManager,
            $storeManager,
            $mathRandom,
            $validator,
            $validationResultsDataFactory,
            $addressRepository,
            $customerMetadataService,
            $customerRegistry,
            $logger,
            $encryptor,
            $configShare,
            $stringHelper,
            $customerRepository,
            $scopeConfig,
            $transportBuilder,
            $dataProcessor,
            $registry,
            $customerViewHelper,
            $dateTime,
            $customerModel,
            $objectFactory,
            $extensibleDataObjectConverter
        );

        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->connection = $connection;
        $this->salesrepCustomerCollectionFactory = $salesrepCustomerCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function resetPassword($email, $resetToken, $newPassword)
    {
        $res = parent::resetPassword($email, $resetToken, $newPassword);
        $this->changeAdminUserPasswordByEmail($email);
        return $res;
    }

    /**
     * @inheritdoc
     */
    public function changePassword($email, $currentPassword, $newPassword)
    {
        $res = parent::changePassword($email, $currentPassword, $newPassword);
        $this->changeAdminUserPasswordByEmail($email);
        return $res;
    }

    /**
     * @inheritdoc
     */
    public function changePasswordById($customerId, $currentPassword, $newPassword)
    {
        $res = parent::changePasswordById($customerId, $currentPassword, $newPassword);
        return $res;
    }

    /**
     * @param $email
     */
    private function changeAdminUserPasswordByEmail($email)
    {
        $customerDataModel = $this->customerRepository->get($email);
        $customer = $this->customerFactory->create()->load($customerDataModel->getId());
        $this->changeAdminUserPasswordByCustomer($customer);
    }

    /**
     * @param $customer
     */
    private function changeAdminUserPasswordByCustomer($customer)
    {
        $connection = $this->connection->getConnection();

        $salesrepCustomersCollection = $this->salesrepCustomerCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('website_id', $customer->getWebsiteId());
        $salesrepCustomersCollection->getSelect()
            ->joinInner(
                ['salesrep_user' => $this->connection->getTableName(\IWD\SalesRep\Model\ResourceModel\User::TABLE_NAME)],
                'main_table.' . SalesrepCustomer::SALESREP_ID . ' = salesrep_user.' . SalesrepUser::SALESREP_ID,
                ['salesrep_user.' . SalesrepUser::ADMIN_ID]
            );

        if ($salesrepCustomersCollection->getSize()) {
            $salesrepCustomer = $salesrepCustomersCollection->getFirstItem();
            // update admin password
            $adminId = $salesrepCustomer->getData(SalesrepUser::ADMIN_ID);
            // NOT TO DO VIA MODEL - \Magento\User\Model\User takes pure password, and converts it to hash, so current hash can't be used
            $connection->update(
                $this->connection->getTableName('admin_user'),
                ['password' => $customer->getData('password_hash')],
                'user_id = ' . $adminId
            );

            // update passwords of all b2b accounts
            $restSlaveCustomers = $this->salesrepCustomerCollectionFactory->create()
                ->addFieldToFilter('salesrep_id', $salesrepCustomer->getSalesrepId())
                ->addFieldToFilter('customer_id', ['neq' => $customer->getId()]);
            foreach ($restSlaveCustomers as $c) {
                $connection->update(
                    $this->connection->getTableName('customer_entity'),
                    ['password_hash' => $customer->getData('password_hash')],
                    'entity_id = ' . $c->getCustomerId()
                );
            }
        }
    }
}
