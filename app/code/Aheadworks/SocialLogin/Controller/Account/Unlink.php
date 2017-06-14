<?php
namespace Aheadworks\SocialLogin\Controller\Account;

use Aheadworks\SocialLogin\Controller\AbstractAction;
use Aheadworks\SocialLogin\Api\AccountRepositoryInterface;
use Aheadworks\SocialLogin\Api\Data\AccountInterface;
use Aheadworks\SocialLogin\Exception\InvalidCustomerException;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Unlink
 */
class Unlink extends AbstractAction
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var AccountRepositoryInterface
     */
    protected $accountRepository;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Aheadworks\SocialLogin\Model\Config\General $generalConfig
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param AccountRepositoryInterface $accountRepository
     * @param CustomerSession $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Aheadworks\SocialLogin\Model\Config\General $generalConfig,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        AccountRepositoryInterface $accountRepository,
        CustomerSession $customerSession
    ) {
        parent::__construct($context, $logger, $generalConfig);
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRepository = $accountRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect;
        }

        $accountId = $this->getRequest()->getParam('account_id');

        try {
            $account = $this->accountRepository->get($accountId);
            $this->validateAction($account);
            $this->accountRepository->delete($account);
            $this->messageManager->addSuccessMessage(__('Social account unlinked'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect;
    }

    /**
     * Validate unlink action
     *
     * @param AccountInterface $account
     * @return void
     * @throws InvalidCustomerException
     */
    protected function validateAction(AccountInterface $account)
    {
        $isValidCustomer = $account->getCustomerId() === $this->customerSession->getCustomerId();
        if (!$isValidCustomer) {
            throw new InvalidCustomerException(__('Invalid customer'));
        }
    }
}
