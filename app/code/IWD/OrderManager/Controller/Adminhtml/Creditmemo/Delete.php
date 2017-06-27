<?php

namespace IWD\OrderManager\Controller\Adminhtml\Creditmemo;

use IWD\OrderManager\Model\Creditmemo\Creditmemo;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Delete
 * @package IWD\OrderManager\Controller\Adminhtml\Creditmemo
 */
class Delete extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_delete_creditmemo';

    /**
     * @var Creditmemo
     */
    private $creditmemo;

    /**
     * @var null|int
     */
    private $orderId = null;

    /**
     * @param Context $context
     * @param Creditmemo $creditmemo
     */
    public function __construct(
        Context $context,
        Creditmemo $creditmemo
    ) {
        parent::__construct($context);
        $this->creditmemo = $creditmemo;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->deleteCreditMemo();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('There was an error when trying to delete the credit memo. Please try again. ') . $e->getMessage()
            );
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->orderId !== null) {
            $resultRedirect->setPath('sales/order/view', ['order_id' => $this->orderId]);
        } else {
            $resultRedirect->setPath('sales/creditmemo/index');
        }

        return $resultRedirect;
    }

    /**
     * @throws \Exception
     */
    protected function deleteCreditMemo()
    {
        $creditmemoId = $this->getCreditMemoId();
        $creditmemo = $this->creditmemo->load($creditmemoId);
        $this->orderId = $creditmemo->getOrderId();
        $incrementId = $creditmemo->getIncrementId();

        if ($creditmemo->isAllowDeleteCreditmemo()) {
            $creditmemo->deleteCreditmemo();
            $this->messageManager->addSuccessMessage(
                __('You have successfully deleted credit memo #%1.', $incrementId)
            );
        } else {
            $this->messageManager->addErrorMessage(
                __('Deletion of credit memos is not permitted. You may enable this option in the Order Manager settings.')
            );
        }
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function getCreditMemoId()
    {
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id', null);
        if (empty($creditmemoId)) {
            throw new LocalizedException(__('Empty param id'));
        }

        return $creditmemoId;
    }
}
