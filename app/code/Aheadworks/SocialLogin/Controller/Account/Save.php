<?php
namespace Aheadworks\SocialLogin\Controller\Account;

use Magento\Framework\Controller\ResultFactory;
use Aheadworks\SocialLogin\Exception\CustomerConvertException;
use Aheadworks\SocialLogin\Controller\AbstractAction;

/**
 * Class Save
 */
class Save extends AbstractAction
{
    /**
     * @var \Aheadworks\SocialLogin\Model\Provider\Customer\ConverterInterface
     */
    private $customerConverter;

    /**
     * @var \Aheadworks\SocialLogin\Helper\State
     */
    private $stateHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Aheadworks\SocialLogin\Model\Config\General $generalConfig
     * @param \Aheadworks\SocialLogin\Model\Provider\Customer\ConverterInterface $customerConverter
     * @param \Aheadworks\SocialLogin\Helper\State $stateHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Aheadworks\SocialLogin\Model\Config\General $generalConfig,
        \Aheadworks\SocialLogin\Model\Provider\Customer\ConverterInterface $customerConverter,
        \Aheadworks\SocialLogin\Helper\State $stateHelper
    ) {
        parent::__construct($context, $logger, $generalConfig);
        $this->customerConverter = $customerConverter;
        $this->stateHelper = $stateHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $response = $this->resultRedirectFactory->create()->setRefererUrl();

        $account = clone $this->stateHelper->getAccount();
        $account->setFirstName($this->getRequest()->getParam('firstname'));
        $account->setLastName($this->getRequest()->getParam('lastname'));
        $account->setEmail($this->getRequest()->getParam('email'));

        try {
            $this->customerConverter->convert($account);
            $this->stateHelper->setAccount($account);
            /** @var \Magento\Framework\Controller\Result\Forward $response */
            $response = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $response->forward('callback_register');
        } catch (CustomerConvertException $e) {
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addErrorMessage($error['message']);
            }
        }

        return $response;
    }
}
