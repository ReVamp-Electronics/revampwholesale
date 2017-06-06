<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml;

use Aheadworks\Helpdesk\Model\Config;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

abstract class Ticket extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Helpdesk::tickets';

    /**
     * Result page factory
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;

        /** @var \Magento\Framework\Message\MessageInterface[] $messages */
        $messages = $this->messageManager->getMessages(true, Config::EMAIL_ERROR_MESSAGE_GROUP)->getItems();
        $uniqueMessages = [];
        foreach ($messages as $message) {
            if (!in_array($message, $uniqueMessages, false)) {
                $uniqueMessages[] = $message;
                $this->messageManager->addMessage($message);
            }
        }
    }

    /**
     * Get result page
     * @return \Magento\Framework\View\Result\Page
     */
    protected function _getResultPage()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
