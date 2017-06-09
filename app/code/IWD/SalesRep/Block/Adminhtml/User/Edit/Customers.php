<?php

namespace IWD\SalesRep\Block\Adminhtml\User\Edit;

use Magento\Framework\Registry;

/**
 * Class Customers
 * @package IWD\SalesRep\Block\Adminhtml\User\Edit
 */
class Customers extends \Magento\Backend\Block\Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Customers constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * @return string
     */
    public function getAttachCustomerUrl()
    {
        return $this->getUrl('salesrep/user/attachCustomer');
    }

    /**
     * @return string
     */
    public function getCommissionBlockUrl()
    {
        return $this->getUrl('salesrep/user/commission');
    }

    /**
     * @return int
     */
    public function getAdminUserId()
    {
        return $this->getRequest()->getParam('user_id');
    }

    /**
     * @return int
     */
    public function getSalesrepId()
    {
        $userModel = $this->registry->registry('permissions_user');
        return $userModel->getData(\IWD\SalesRep\Model\Preference\ResourceModel\User\User::FIELD_NAME_SALESREPID);
    }
}
