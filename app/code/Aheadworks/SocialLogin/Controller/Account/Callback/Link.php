<?php
namespace Aheadworks\SocialLogin\Controller\Account\Callback;

use Aheadworks\SocialLogin\Exception\InvalidSocialAccountException;
use Aheadworks\SocialLogin\Model\Provider\Account\ConverterInterface;
use Aheadworks\SocialLogin\Api\AccountRepositoryInterface;
use Aheadworks\SocialLogin\Controller\Account\Callback;
use Aheadworks\SocialLogin\Helper\State;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class Link
 */
class Link extends Callback
{
    /**
     * @var AccountRepositoryInterface
     */
    protected $accountRepository;

    /**
     * @var ConverterInterface
     */
    protected $converter;

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
     * @param ConverterInterface $converter
     * @param CustomerSession $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Aheadworks\SocialLogin\Model\Config\General $generalConfig,
        \Aheadworks\SocialLogin\Model\ProviderManagement $providerManagement,
        State $stateHelper,
        AccountRepositoryInterface $accountRepository,
        ConverterInterface $converter,
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
        $this->converter = $converter;
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $response = $this->resultRedirectFactory->create()->setPath('social/customer/accounts');

        try {
            $providerAccount = $this->stateHelper->getAccount();

            $account = $this->converter->convert($providerAccount);
            $account->setCustomerId($this->customerSession->getCustomerId());
            $this->accountRepository->save($account);

            $this->stateHelper->clear();

        } catch (InvalidSocialAccountException $e) {
            $this->messageManager->addErrorMessage(__('Social account already taken'));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $this->getRequest()->getParams());
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
            $response = $this->resultRedirectFactory->create()->setPath('/');
        }

        return $response;
    }
}
