<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\History;

use IWD\OrderManager\Model\Order\History;
use IWD\OrderManager\Helper\Data;
use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Model\Order\Order;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Delete
 * @package IWD\OrderManager\Controller\Adminhtml\Order\History
 */
class Delete extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_comments_delete';

    /**
     * @var \IWD\OrderManager\Model\Order\Order
     */
    private $order;

    /**
     * @var \IWD\OrderManager\Model\Order\History
     */
    private $history;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param History $history
     * @param Order $order
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        History $history,
        Order $order
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $helper,
            AbstractAction::ACTION_CHECK_UPDATE
        );
        $this->history = $history;
        $this->order = $order;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getResultHtml()
    {
        $id = $this->getCommentId();

        $this->history->getCommentById($id)
            ->setOrder($this->getOrder())
            ->deleteComment();

        return __('Comment was removed successfully.');
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function getCommentId()
    {
        $id = $this->getRequest()->getParam('id', null);
        if (empty($id)) {
            throw new LocalizedException(__('Empty param id'));
        }

        return $id;
    }

    /**
     * @return Order
     * @throws \Exception
     */
    protected function getOrder()
    {
        $id = $this->getOrderId();
        $this->order->load($id);

        if (!$this->order->getEntityId()) {
            throw new LocalizedException(__('Can not load order'));
        }

        return $this->order;
    }
}
