<?php

namespace IWD\OrderManager\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Backend\Model\Auth\Session;

/**
 * Class SaveOrderStatusHistoryObserver
 * @package IWD\OrderManager\Observer
 */
class SaveOrderStatusHistoryObserver implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\Order\Status\History
     */
    private $statusHistory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * SaveOrderStatusHistoryObserver constructor.
     * @param Session $authSession
     */
    public function __construct(Session $authSession)
    {
        $this->authSession = $authSession;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $this->statusHistory = $observer->getEvent()->getStatusHistory();

        $this->fixNewLineToBr();
        $this->assignAdminUserToComment();
    }

    /**
     * Change new line \r\n to <br/>
     */
    private function fixNewLineToBr()
    {
        $comment = $this->statusHistory->getComment();
        $comment = nl2br($comment);
        $this->statusHistory->setComment($comment);
    }

    /**
     * Assign admin user to comment
     */
    private function assignAdminUserToComment()
    {
        $user = $this->authSession->getUser();

        if ($user) {
            $this->statusHistory->setAdminId($user->getId());
            $this->statusHistory->setAdminEmail($user->getEmail());
        }
    }
}
