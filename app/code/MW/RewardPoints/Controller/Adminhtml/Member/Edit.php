<?php

namespace MW\RewardPoints\Controller\Adminhtml\Member;

class Edit extends \MW\RewardPoints\Controller\Adminhtml\Member
{
    /**
     * Member account edit page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
    	$customerId = $this->getRequest()->getParam('id');
        $this->_objectManager->get('MW\RewardPoints\Helper\Data')->checkAndInsertCustomerId($customerId, 0);
        $customer  = $this->_objectManager->get('MW\RewardPoints\Model\Customer')->load($customerId);

        if ($customer->getId() || $customerId == 0) {
            $data = $this->_session->getFormData(true);
            if (!empty($data)) {
                $customer->setData($data);
            }

            $this->_objectManager->get('Magento\Framework\Registry')->register(
                'rewardpoints_data_member',
                $customer
            );

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('MW_RewardPoints::rewardpoints');
            $resultPage->getConfig()->getTitle()->prepend(__('Reward Points Member Information'));
            return $resultPage;
        } else {
            $this->messageManager->addError(__('Member does not exist'));
            $this->_redirect('*/*/');
        }
    }
}
