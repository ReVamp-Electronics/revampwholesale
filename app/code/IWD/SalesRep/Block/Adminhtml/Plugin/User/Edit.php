<?php

namespace IWD\SalesRep\Block\Adminhtml\Plugin\User;

use \IWD\SalesRep\Helper\Data as SalesrepHelper;

/**
 * Class Edit
 * @package IWD\SalesRep\Block\Adminhtml\Plugin\User
 */
class Edit
{
    /**
     * @param \Magento\User\Block\User\Edit $subject
     * @param \Closure $proceed
     * @return mixed|string
     */
    public function aroundGetBackUrl(\Magento\User\Block\User\Edit $subject, \Closure $proceed)
    {
        if ($subject->getRequest()->getParam(SalesrepHelper::HTTP_REFERRER_KEY) == SalesrepHelper::HTTP_REFERRER) {
            return $subject->getUrl('salesrep/salesrep/index');
        }

        return $proceed();
    }
}
