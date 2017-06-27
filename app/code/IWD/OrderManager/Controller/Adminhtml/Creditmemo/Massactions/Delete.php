<?php

namespace IWD\OrderManager\Controller\Adminhtml\Creditmemo\Massactions;

use IWD\OrderManager\Model\Creditmemo\Creditmemo;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory;

/**
 * Class Delete
 * @package IWD\OrderManager\Controller\Adminhtml\Creditmemo\Massactions
 */
class Delete extends AbstractMassAction
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
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Creditmemo $creditmemo
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Creditmemo $creditmemo
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->creditmemo = $creditmemo;
    }

    /**
     * {@inheritdoc}
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countDeletedCreditMemos = 0;
        foreach ($collection->getItems() as $item) {
            $creditmemo = clone $this->creditmemo->load($item->getId());
            if ($creditmemo->isAllowDeleteCreditmemo()) {
                $creditmemo->deleteCreditmemo();
                $countDeletedCreditMemos++;
            }
        }
        $countNonCancelOrder = count($collection->getItems()) - $countDeletedCreditMemos;

        if ($countNonCancelOrder && $countDeletedCreditMemos) {
            $this->messageManager->addErrorMessage(
                __('Credit memo %1 could not be deleted as deletion of credit memos is not permitted. You may enable this option in the Order Manager settings.', $countNonCancelOrder)
            );
        } elseif ($countNonCancelOrder) {
            $this->messageManager->addErrorMessage(
                __('Credit memo could not be deleted as deletion of credit memos is not permitted. You may enable this option in the Order Manager settings.')
            );
        }

        if ($countDeletedCreditMemos) {
            $this->messageManager->addSuccessMessage(__('Credit memo %1 has been deleted.', $countDeletedCreditMemos));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }

    /**
     * @return string
     */
    protected function getComponentRefererUrl()
    {
        return 'sales/creditmemo';
    }
}
