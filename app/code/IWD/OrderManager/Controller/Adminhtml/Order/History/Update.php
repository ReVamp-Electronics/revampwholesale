<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\History;

use IWD\OrderManager\Model\Order\History;
use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Helper\Data;
use IWD\OrderManager\Model\Order\Order;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

class Update extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_comments_edit';

    /**
     * @var \IWD\OrderManager\Model\Order\Order
     */
    protected $_order;

    /**
     * @var \IWD\OrderManager\Model\Order\History
     */
    protected $_history;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param Order $order
     * @param History $history
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        Order $order,
        History $history
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $helper,
            AbstractAction::ACTION_UPDATE
        );
        $this->_history = $history;
        $this->_order = $order;
    }

    /**
     * @return mixed|string
     * @throws \Exception
     */
    protected function getResultHtml()
    {
        $id = $this->getCommentId();
        $commentText = $this->getCommentText();
        $isVisibleOnFront = $this->getIsVisibleOnFront();

        $this->_history->getCommentById($id)
            ->setOrder($this->getOrder())
            ->updateComment($commentText, $isVisibleOnFront);

        return $commentText;
    }

    /**
     * @return Order
     * @throws LocalizedException
     */
    protected function getOrder()
    {
        $id = $this->getOrderId();
        $this->_order->load($id);
        if (!$this->_order->getEntityId()) {
            throw new LocalizedException(__('Can not load order'));
        }
        return $this->_order;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    protected function getCommentId()
    {
        $id = $this->getRequest()->getParam('id', null);

        if (empty($id)) {
            throw new LocalizedException(__('Empty param id'));
        }

        return $id;
    }

    /**
     * @return mixed|string
     */
    protected function getCommentText()
    {
        $comment = $this->getRequest()->getParam('comment', '');
        $comment = trim(strip_tags($comment));
        $comment = nl2br($comment);

        return $comment;
    }

    /**
     * @return int
     */
    protected function getIsVisibleOnFront()
    {
        return $this->getRequest()->getParam('is_visible_on_front', null)? 1 : 0;
    }
}
