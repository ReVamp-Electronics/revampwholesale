<?php
namespace Aheadworks\SocialLogin\Model\Provider\Account;

use Aheadworks\SocialLogin\Model\Provider\AccountInterface;
use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;

interface RetrieverInterface
{
    /**
     * @param ServiceInterface $service
     * @return AccountInterface
     */
    public function retrieve(ServiceInterface $service);
}
