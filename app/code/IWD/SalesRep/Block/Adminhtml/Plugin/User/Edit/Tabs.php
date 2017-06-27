<?php

namespace IWD\SalesRep\Block\Adminhtml\Plugin\User\Edit;

/**
 * Class Tabs
 * @package IWD\SalesRep\Block\Adminhtml\Plugin\User\Edit
 */
class Tabs
{
    /**
     * @param \Magento\User\Block\User\Edit\Tabs $subject
     * @param \Closure $proceed
     * @param $tabId
     * @param $tab
     * @return mixed
     */
    public function aroundAddTab(\Magento\User\Block\User\Edit\Tabs $subject, \Closure $proceed, $tabId, $tab)
    {
        $result = $proceed($tabId, $tab);
        
        if ($tabId == 'roles_section') {
            $subject->addTab(
                'salesrep_user',
                [
                    'label' => __('Sales Representative'),
                    'title' => __('Sales Representative'),
                    'content' => $subject->getLayout()->createBlock(
                        '\IWD\SalesRep\Block\Adminhtml\User\Edit\Tab\SalesRep',
                        'salesrep'
                    )->toHtml()
                ]
            );

            $subject->addTab(
                'salesrep_customers',
                [
                    'label' => __('Assigned Customers'),
                    'url' => $subject->getUrl('salesrep/user/customers', ['_current' => true]),
                    'class' => 'ajax',
                ]
            );
        }
        
        return $result;
    }
}
