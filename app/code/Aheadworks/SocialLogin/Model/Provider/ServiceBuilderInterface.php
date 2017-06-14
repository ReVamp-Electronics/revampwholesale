<?php
namespace Aheadworks\SocialLogin\Model\Provider;

use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;

/**
 * Interface ServiceBuilderInterface
 */
interface ServiceBuilderInterface
{
    /**
     * @return ServiceInterface
     */
    public function build();
}
