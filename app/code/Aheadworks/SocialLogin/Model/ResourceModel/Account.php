<?php
namespace Aheadworks\SocialLogin\Model\ResourceModel;

/**
 * Account Resource
 */
class Account extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('social_account', 'account_id');
    }
}
