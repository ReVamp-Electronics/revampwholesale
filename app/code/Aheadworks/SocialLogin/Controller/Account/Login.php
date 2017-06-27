<?php
namespace Aheadworks\SocialLogin\Controller\Account;

use Aheadworks\SocialLogin\Helper\State;

/**
 * Class Login
 */
class Login extends AbstractLogin
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->stateHelper->setState(State::STATE_LOGIN);

        return parent::execute();
    }
}
