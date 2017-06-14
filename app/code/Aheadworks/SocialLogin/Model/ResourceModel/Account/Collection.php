<?php
namespace Aheadworks\SocialLogin\Model\ResourceModel\Account;

/**
 * Class Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Aheadworks\SocialLogin\Model\Account', 'Aheadworks\SocialLogin\Model\ResourceModel\Account');
    }
}
