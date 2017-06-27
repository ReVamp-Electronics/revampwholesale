<?php
namespace Aheadworks\SocialLogin\Controller\Customer;

/**
 * Class Accounts
 */
class Accounts extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Social Accounts'));
        return $resultPage;
    }
}
