<?php
namespace Aheadworks\SocialLogin\Controller\Account;

use Aheadworks\SocialLogin\Controller\AbstractAction;

/**
 * Class AbstractLogin
 */
abstract class AbstractLogin extends AbstractAction
{
    /**
     * @var \Aheadworks\SocialLogin\Model\ProviderManagement
     */
    protected $providerManagement;

    /**
     * @var \Aheadworks\SocialLogin\Helper\State
     */
    protected $stateHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Aheadworks\SocialLogin\Model\Config\General $generalConfig
     * @param \Aheadworks\SocialLogin\Model\ProviderManagement $providerManagement
     * @param \Aheadworks\SocialLogin\Helper\State $stateHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Aheadworks\SocialLogin\Model\Config\General $generalConfig,
        \Aheadworks\SocialLogin\Model\ProviderManagement $providerManagement,
        \Aheadworks\SocialLogin\Helper\State $stateHelper
    ) {
        parent::__construct($context, $logger, $generalConfig);
        $this->providerManagement = $providerManagement;
        $this->stateHelper = $stateHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $providerCode = $this->getRequest()->getParam('provider');

        try {
            $factory = $this->providerManagement->getEnabledFactory($providerCode);

            $response = $factory->createLoginRequestProcessor()
                ->process($factory->createService(), $this->getRequest());

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $this->getRequest()->getParams());
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
            $response = $this->resultRedirectFactory->create()->setPath('/');
        }

        return $response;
    }
}
