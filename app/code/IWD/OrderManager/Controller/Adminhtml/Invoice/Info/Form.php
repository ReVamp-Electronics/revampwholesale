<?php

namespace IWD\OrderManager\Controller\Adminhtml\Invoice\Info;

use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Form
 * @package IWD\OrderManager\Controller\Adminhtml\Invoice\Info
 */
class Form extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Magento_Backend::admin';


    /**
     * @return string
     * @throws \Exception
     */
    protected function getResultHtml()
    {
        $resultPage = $this->resultPageFactory->create();

        /** @var \IWD\OrderManager\Block\Adminhtml\Invoice\Info\Form $infoFormContainer */
        $infoFormContainer = $resultPage->getLayout()->getBlock('iwdordermamager_invoice_info_form');
        if (empty($infoFormContainer)) {
            throw new LocalizedException(__('Can not load block'));
        }

        $invoiceId = $this->getInvoiceId();
        $infoFormContainer->setInvoiceId($invoiceId);

        return $infoFormContainer->toHtml();
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function getInvoiceId()
    {
        $id = $this->getRequest()->getParam('invoice_id', null);
        if (empty($id)) {
            throw new LocalizedException(__('Empty param invoice id'));
        }
        return $id;
    }
}
