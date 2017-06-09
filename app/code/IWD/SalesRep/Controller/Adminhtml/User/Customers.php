<?php

namespace IWD\SalesRep\Controller\Adminhtml\User;

use Magento\Framework\Registry;

/**
 * Class Customers
 * @package IWD\SalesRep\Controller\Adminhtml\User
 */
class Customers extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    private $resultLayoutFactory;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * Customers constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\User\Model\UserFactory $userFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->userFactory = $userFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $_user = $this->userFactory->create()->load($this->getRequest()->getParam('user_id'));
        $this->registry->register('admin_user', $_user);

        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('user.salesrep.customers');

        return $resultLayout;
    }
}
