<?php

namespace MW\RewardPoints\Controller\Adminhtml\Report;

class Dashboard extends \MW\RewardPoints\Controller\Adminhtml\Report
{
    /**
     * Dashboard report page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
    	/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        if ($this->getRequest()->getPost('ajax') == 'true') {
            $data = $this->getRequest()->getParams();
            $reportModel = $this->_objectManager->get('MW\RewardPoints\Model\Report');

            switch($this->getRequest()->getParam('type')) {
                case 'dashboard':
                    print $reportModel->prepareCollection($data);
                    break;
                case 'circle':
                    print $reportModel->preapareCollectionPieChart($data);
                    break;
            }

            exit;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_RewardPoints::rewardpoints');
        $resultPage->getConfig()->getTitle()->prepend(__('Rewardpoints Dashboard'));
        return $resultPage;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_RewardPoints::overview');
    }
}
