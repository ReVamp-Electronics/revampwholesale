<?php
namespace Aheadworks\SocialLogin\Controller\Account;

use Aheadworks\SocialLogin\Helper\State;

/**
 * Class Link
 */
class Link extends AbstractLogin
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->stateHelper->setState(State::STATE_LINK);

        return parent::execute();
    }
}
