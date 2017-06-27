<?php

namespace IWD\SalesRep\Controller\Adminhtml\Salesrep;

use \IWD\SalesRep\Model\User as SalesrepUser;

/**
 * Class MassDelete
 * @package IWD\SalesRep\Controller\Adminhtml\Salesrep
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var \IWD\SalesRep\Model\ResourceModel\User\CollectionFactory
     */
    private $salesrepUserCollectionFactory;

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private $adminUserCollectionFactory;

    /**
     * MassDelete constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \IWD\SalesRep\Model\ResourceModel\User\CollectionFactory $salesrepUserCollectionFactory
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \IWD\SalesRep\Model\ResourceModel\User\CollectionFactory $salesrepUserCollectionFactory,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
    ) {
        parent::__construct($context);
        $this->salesrepUserCollectionFactory = $salesrepUserCollectionFactory;
        $this->adminUserCollectionFactory = $userCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $salesrepIds = $this->getRequest()->getParam('entity_id');
        $deleteAllAccounts = $this->getRequest()->getParam('all_accounts', false);
        $salesrepUsers = $this->salesrepUserCollectionFactory->create()
            ->addFieldToFilter(SalesrepUser::SALESREP_ID, ['in' => $salesrepIds])
            ->getItems();

        $deletedCnt = 0;
        $adminUserIds = [];

        foreach ($salesrepUsers as $salesrepUser) {
            $adminUserIds[] = $salesrepUser->getData(SalesrepUser::ADMIN_ID);
            $salesrepUser->delete();
            $deletedCnt++;
        }

        /* delete related admin users, if requested */
        if ($deleteAllAccounts) {
            $adminUsers = $this->adminUserCollectionFactory->create()
                ->addFieldToFilter('user_id', ['in' => $adminUserIds]);

            foreach ($adminUsers as $adminUser) {
                $adminUser->delete();
            }
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $deletedCnt)
        );
        $this->_redirect('*/*/index');
    }
}
