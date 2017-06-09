<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\History;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\User\Model\User;

/**
 * Class AdminName
 * @package IWD\OrderManager\Block\Adminhtml\Order\History
 */
class AdminName extends Template
{
    /**
     * @var \IWD\OrderManager\Model\Order\Item
     */
    private $item;

    /**
     * @var User
     */
    private $user;

    /**
     * @var []
     */
    private $users;

    /**
     * AdminName constructor.
     * @param Context $context
     * @param User $user
     * @param array $data
     */
    public function __construct(
        Context $context,
        User $user,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->user = $user;
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Item $item
     * @return $this
     */
    public function setItem($item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @return \IWD\OrderManager\Model\Order\Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return string
     */
    public function getAdminUsername()
    {
        $adminId = $this->getItem()->getAdminId();
        $adminUsername = '';
        if (!empty($adminId)) {
            $user = $this->getAdminUser($adminId);
            $adminUsername = ucfirst($user->getFirstname()) . ' ' . ucfirst($user->getLastname());
        }

        return $adminUsername;
    }

    /**
     * @return string
     */
    public function getAdminEmail()
    {
        return $this->getItem()->getAdminEmail();
    }

    /**
     * @param $adminId
     */
    private function getAdminUser($adminId)
    {
        if (!isset($this->users[$adminId])) {
            $this->users[$adminId] = $this->user->load($adminId);
        }

        return $this->users[$adminId];
    }
}
