<?php
namespace Aheadworks\SocialLogin\Controller\Account;

use Aheadworks\SocialLogin\Controller\AbstractAction;
use Aheadworks\SocialLogin\Exception\InvalidStateException;
use Aheadworks\SocialLogin\Helper\State;

/**
 * Class Callback
 */
class Callback extends AbstractAction
{
    /**
     * @var \Aheadworks\SocialLogin\Model\ProviderManagement
     */
    protected $providerManagement;

    /**
     * @var State
     */
    protected $stateHelper;

    /**
     * @var array
     */
    protected $forwardsMap;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Aheadworks\SocialLogin\Model\Config\General $generalConfig
     * @param \Aheadworks\SocialLogin\Model\ProviderManagement $providerManagement
     * @param State $stateHelper
     * @param array $forwardsMap
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Aheadworks\SocialLogin\Model\Config\General $generalConfig,
        \Aheadworks\SocialLogin\Model\ProviderManagement $providerManagement,
        State $stateHelper,
        array $forwardsMap = []
    ) {
        parent::__construct($context, $logger, $generalConfig);
        $this->providerManagement = $providerManagement;
        $this->stateHelper = $stateHelper;
        $this->forwardsMap = $forwardsMap;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $providerCode = $this->getRequest()->getParam('provider');

        $response = null;
        try {

            $factory = $this->providerManagement->getEnabledFactory($providerCode);
            $service = $factory->createService();

            $account = $factory->createCallbackRequestProcessor()
                ->process($service, $this->getRequest());

            $this->stateHelper->setAccount($account);

            $this->forwardToOriginalAction();

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $this->getRequest()->getParams());
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
            $response = $this->resultRedirectFactory->create()->setPath('/');
        }

        return $response;
    }

    /**
     * Forward callback to original action
     *
     * @return void
     * @throws InvalidStateException
     */
    protected function forwardToOriginalAction()
    {
        $this->_forward($this->getOriginalAction());
    }

    /**
     * Get original action
     *
     * @return string
     * @throws InvalidStateException
     */
    protected function getOriginalAction()
    {
        $state = $this->stateHelper->getState();
        if (!isset($this->forwardsMap[$state])) {
            throw new InvalidStateException(__('Invalid state for action'));
        }
        return $this->forwardsMap[$state];
    }
}
