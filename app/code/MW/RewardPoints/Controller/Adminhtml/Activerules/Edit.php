<?php

namespace MW\RewardPoints\Controller\Adminhtml\Activerules;

class Edit extends \MW\RewardPoints\Controller\Adminhtml\Activerules
{
    /**
     * Customer Behavior Rule edit page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
    	$id    = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->get('MW\RewardPoints\Model\Activerules')->load($id);

        if ($model->getId() || $id == 0) {
        	$data = $this->_session->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            $this->_objectManager->get('Magento\Framework\Registry')->register(
                'data_activerules',
                $model
            );

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('MW_RewardPoints::rewardpoints');
            $resultPage->getConfig()->getTitle()->prepend(__('Customer Behavior Rules'));
            return $resultPage;
        } else {
            $this->messageManager->addError(__('Rule does not exist'));
            $this->_redirect('*/*/');
        }
    }
}
