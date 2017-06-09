<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\History;

use IWD\OrderManager\Model\Order\History;
use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Form
 * @package IWD\OrderManager\Controller\Adminhtml\Order\History
 */
class Form extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_comments_edit';

    /**
     * @var \IWD\OrderManager\Model\Order\History
     */
    private $history;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param History $history
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        History $history
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $helper,
            AbstractAction::ACTION_GET_FORM
        );
        $this->history = $history;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getResultHtml()
    {
        $resultPage = $this->resultPageFactory->create();

        /**
         * @var $block \IWD\OrderManager\Block\Adminhtml\Order\History\Form
         */
        $block = $resultPage->getLayout()->getBlock('iwdordermamager_order_history_form');
        if (empty($block)) {
            throw new LocalizedException(__('Can not load block'));
        }

        $id = $this->getCommentId();
        $item = $this->history->getCommentById($id);

        $block->setCommentItem($item);

        return $block->toHtml();
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function getCommentId()
    {
        $id = $this->getRequest()->getParam('id', null);

        if (empty($id)) {
            throw new LocalizedException(__('Empty param id'));
        }

        return $id;
    }
}
