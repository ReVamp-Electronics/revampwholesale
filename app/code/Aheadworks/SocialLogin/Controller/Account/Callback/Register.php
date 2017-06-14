<?php
namespace Aheadworks\SocialLogin\Controller\Account\Callback;

use Aheadworks\SocialLogin\Model\Provider\Account\ConverterInterface;
use Aheadworks\SocialLogin\Model\Provider\AccountInterface as ProviderAccountInterface;
use Aheadworks\SocialLogin\Model\Provider\Customer\ConverterInterface as CustomerConverterInterface;
use Aheadworks\SocialLogin\Api\AccountRepositoryInterface;
use Aheadworks\SocialLogin\Controller\Account\Callback;
use Aheadworks\SocialLogin\Helper\State;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Register
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Register extends Callback
{
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AccountRepositoryInterface
     */
    protected $accountRepository;

    /**
     * @var ConverterInterface
     */
    protected $converter;

    /**
     * @var CustomerConverterInterface
     */
    protected $customerConverter;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Aheadworks\SocialLogin\Model\Config\General $generalConfig
     * @param \Aheadworks\SocialLogin\Model\ProviderManagement $providerManagement
     * @param State $stateHelper
     * @param AccountRepositoryInterface $accountRepository
     * @param ConverterInterface $converter
     * @param CustomerConverterInterface $customerConverter
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Aheadworks\SocialLogin\Model\Config\General $generalConfig,
        \Aheadworks\SocialLogin\Model\ProviderManagement $providerManagement,
        State $stateHelper,
        AccountRepositoryInterface $accountRepository,
        ConverterInterface $converter,
        CustomerConverterInterface $customerConverter,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct(
            $context,
            $logger,
            $generalConfig,
            $providerManagement,
            $stateHelper
        );
        $this->accountRepository = $accountRepository;
        $this->converter = $converter;
        $this->customerConverter = $customerConverter;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $response = null;

        try {
            $providerAccount = $this->stateHelper->getAccount();

            $customer = $this->customerConverter->convert($providerAccount);
            $customer = $this->initCustomer($customer);

            $this->linkAccount($providerAccount, $customer);

            $this->_forward('callback_login');
        } catch (\Aheadworks\SocialLogin\Exception\CustomerConvertException $e) {
            $this->messageManager->addWarningMessage(__('Please, fill out the fields below'));
            $response = $this->resultRedirectFactory->create()->setPath('social/account/edit');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $this->getRequest()->getParams());
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
            $response = $this->resultRedirectFactory->create()->setPath('/');
        }

        return $response;
    }

    /**
     * @param ProviderAccountInterface $providerAccount
     * @param CustomerInterface $customer
     * @return \Aheadworks\SocialLogin\Api\Data\AccountInterface
     */
    protected function linkAccount(ProviderAccountInterface $providerAccount, CustomerInterface $customer)
    {
        $account = $this->converter->convert($providerAccount);
        $account->setCustomerId($customer->getId());
        return $this->accountRepository->save($account);
    }

    /**
     * Init customer
     *
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    protected function initCustomer(CustomerInterface $customer)
    {
        try {
            $customer = $this->customerRepository->get($customer->getEmail());
        } catch (NoSuchEntityException $e) {
            $customer = $this->customerAccountManagement->createAccount($customer);
        }
        return $customer;
    }
}
