<?php

namespace IWD\SalesRep\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class AdminUserEditPredispatchObserver
 * @package IWD\SalesRep\Observer\Backend
 */
class AdminUserEditPredispatchObserver implements ObserverInterface
{
    /**
     * User model factory
     *
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;
    
    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    /**
     * AdminUserEditPredispatchObserver constructor.
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Backend\Model\Session $session
     */
    public function __construct(
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Backend\Model\Session $session
    ) {
        $this->userFactory = $userFactory;
        $this->session = $session;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $is_sr_op = $this->session->getIsSROperation();
        if ($is_sr_op != 'yes') {
            return $this;
        }
        
        $request = $observer->getControllerAction()->getRequest();
        
        $userId = $request->getParam('user_id');
        /** @var \Magento\User\Model\User $model */
        $model = $this->userFactory->create();
        
        if ($userId) {
            $model->load($userId);
            if (!$model->getId()) {
                $observer->getControllerAction()->getRequest()->setParam('user_id', null);
                return $this;
            }
        }
        
        return $this;
    }
}
