<?php

namespace IWD\SalesRep\Block\Adminhtml\Plugin\User\Tab;

/**
 * Class Main
 * @package IWD\SalesRep\Block\Adminhtml\Plugin\User\Tab
 */
class Main
{
    public function afterGetForm(\Magento\User\Block\User\Edit\Tab\Main $subject, \Magento\Framework\Data\Form $result)
    {
        $fieldset = $result->getElement('current_user_verification_fieldset');
        $fieldset->setData('legend', 'Current Admin Password');
        $el = $fieldset->getElements()->searchById(\Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD);
        $el->setData('note', 'Enter admin password in order to make any changes');
        return $result;
    }
}
