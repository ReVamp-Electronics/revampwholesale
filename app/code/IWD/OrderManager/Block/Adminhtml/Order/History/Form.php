<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\History;

use Magento\Backend\Block\Template;

/**
 * Class Form
 * @package IWD\OrderManager\Block\Adminhtml\Order\History
 */
class Form extends Template
{
    /**
     * @var \Magento\Sales\Model\Order\Status\History
     */
    private $comment;

    /**
     * @param \Magento\Sales\Model\Order\Status\History $comment
     * @return $this
     */
    public function setCommentItem($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Order\Status\History
     */
    public function getCommentItem()
    {
        return $this->comment;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        $comment = $this->getCommentItem()->getComment();
        $breaks = ["<br />","<br>","<br/>"];

        return str_ireplace($breaks, "", $comment);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getCommentItem()->getId();
    }

    /**
     * @return bool
     */
    public function getIsVisibleOnFront()
    {
        return $this->getCommentItem()->getIsVisibleOnFront();
    }
}
