<?php
namespace Aheadworks\SocialLogin\Controller;

use Magento\Framework\App\RequestInterface;

/**
 * Class AbstractAction
 */
abstract class AbstractAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Aheadworks\SocialLogin\Model\Config\General
     */
    protected $generalConfig;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Aheadworks\SocialLogin\Model\Config\General $generalConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Aheadworks\SocialLogin\Model\Config\General $generalConfig
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->generalConfig = $generalConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->isAllowed()) {
            /** @var \Magento\Framework\App\Response\Http $response */
            $response = $this->getResponse();
            $response->setRedirect('/');
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Is action allowed
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->generalConfig->isModuleEnabled();
    }
}
