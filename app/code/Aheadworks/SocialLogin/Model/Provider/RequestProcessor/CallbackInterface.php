<?php
namespace Aheadworks\SocialLogin\Model\Provider\RequestProcessor;

use Aheadworks\SocialLogin\Model\Provider\AccountInterface;
use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;

/**
 * Interface CallbackInterface
 */
interface CallbackInterface
{
    /**
     * @param ServiceInterface $service
     * @param \Magento\Framework\App\RequestInterface $request
     * @return AccountInterface
     */
    public function process(ServiceInterface $service, \Magento\Framework\App\RequestInterface $request);
}
