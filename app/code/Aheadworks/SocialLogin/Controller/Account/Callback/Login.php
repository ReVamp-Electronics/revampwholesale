<?php
namespace Aheadworks\SocialLogin\Controller\Account\Callback;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Aheadworks\SocialLogin\Api\AccountRepositoryInterface;
use Aheadworks\SocialLogin\Controller\Account\Callback;
use Aheadworks\SocialLogin\Exception\InvalidCustomerException;
use Aheadworks\SocialLogin\Helper\State;
use Aheadworks\SocialLogin\Model\Customer\AccountManagement;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Login
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Login extends Callback
{
    /**
     * @var AccountRepositoryInterface
     */
    protected $accountRepository;

    /**
     * @var AccountManagement
     */
    protected $accountManagement;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Aheadworks\SocialLogin\Model\Config\General $generalConfig
     * @param \Aheadworks\SocialLogin\Model\ProviderManagement $providerManagement
     * @param State $stateHelper
     * @param AccountRepositoryInterface $accountRepository
     * @param AccountManagement $accountManagement
     * @param AccountRedirect $accountRedirect
     * @param CustomerSession $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Aheadworks\SocialLogin\Model\Config\General $generalConfig,
        \Aheadworks\SocialLogin\Model\ProviderManagement $providerManagement,
        State $stateHelper,
        AccountRepositoryInterface $accountRepository,
        AccountManagement $accountManagement,
        AccountRedirect $accountRedirect,
        CustomerSession $customerSession
    ) {
        parent::__construct(
            $context,
            $logger,
            $generalConfig,
            $providerManagement,
            $stateHelper
        );
        $this->accountRepository = $accountRepository;
        $this->accountManagement = $accountManagement;
        $this->accountRedirect = $accountRedirect;
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $response = null;

        try {
            $account = $this->stateHelper->getAccount();

            $socialAccount = $this->accountRepository->getBySocialId($account->getType(), $account->getSocialId());
            $customer = $this->accountManagement->authenticate($socialAccount);

            $this->accountRepository->save($socialAccount->updateLastSignedAt());
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $this->customerSession->regenerateId();

            $this->stateHelper->clear();

            $response = $this->accountRedirect->getRedirect();
        } catch (NoSuchEntityException $e) {
            $this->_forward('callback_register');
        } catch (InvalidCustomerException $e) {
            $this->messageManager->addErrorMessage(__('Social account linked to undefined customer.'));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $this->getRequest()->getParams());
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
            $response = $this->resultRedirectFactory->create()->setPath('/');
        }

        return $response;
    }
}
