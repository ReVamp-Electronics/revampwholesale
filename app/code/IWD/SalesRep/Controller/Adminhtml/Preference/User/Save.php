<?php

namespace IWD\SalesRep\Controller\Adminhtml\Preference\User;

use \IWD\SalesRep\Helper\Data as SalesrepHelper;

/**
 * Class Save
 * @package IWD\SalesRep\Controller\Adminhtml\Preference\User
 */
class Save extends \Magento\User\Controller\Adminhtml\User\Save
{
    /**
     * @inheritdoc
     */
    protected function _redirect($path, $arguments = [])
    {
        $salesrep = $this->_request->getParam('salesrep');

        if ($salesrep
            && isset($salesrep[SalesrepHelper::HTTP_REFERRER_KEY])
            && $salesrep[SalesrepHelper::HTTP_REFERRER_KEY] == \IWD\SalesRep\Helper\Data::HTTP_REFERRER
        ) {
            switch ($path) {
                case 'adminhtml/*/':
                    $path = 'salesrep/salesrep/index';
                    break;
                case 'adminhtml/*/edit':
                case 'adminhtml/*/new':
                    $arguments[SalesrepHelper::HTTP_REFERRER_KEY] = \IWD\SalesRep\Helper\Data::HTTP_REFERRER;
                    break;
            }
        }
        return parent::_redirect($path, $arguments);
    }
}
