<?php

namespace IWD\OrderManager\Controller\Adminhtml\Creditmemo\Info;

use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Form
 * @package IWD\OrderManager\Controller\Adminhtml\Creditmemo\Info
 */
class Form extends AbstractAction
{
    /**
     * @return string
     * @throws \Exception
     */
    protected function getResultHtml()
    {
        $resultPage = $this->resultPageFactory->create();

        /** @var \IWD\OrderManager\Block\Adminhtml\Creditmemo\Info\Form $infoFormContainer */
        $infoFormContainer = $resultPage->getLayout()->getBlock('iwdordermamager_creditmemo_info_form');
        if (empty($infoFormContainer)) {
            throw new LocalizedException(__('Can not load block'));
        }

        $creditmemoId = $this->getCreditmemoId();
        $infoFormContainer->setCreditmemoId($creditmemoId);

        return $infoFormContainer->toHtml();
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function getCreditmemoId()
    {
        $id = $this->getRequest()->getParam('creditmemo_id', null);
        if (empty($id)) {
            throw new LocalizedException(__('Empty param creditmemo id'));
        }
        return $id;
    }
}
