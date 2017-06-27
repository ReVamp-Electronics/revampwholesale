<?php

namespace MW\RewardPoints\Controller\Adminhtml\Cartrules;

class Edit extends \MW\RewardPoints\Controller\Adminhtml\Cartrules
{
    /**
     * Shopping Cart Reward Rule edit page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
    	$id    = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->get('MW\RewardPoints\Model\Cartrules')->load($id);

        if ($model->getId() || $id == 0) {
        	$data = $this->_session->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
            $model->getActions()->setJsFormObject('rule_actions_fieldset');
            $this->_objectManager->get('Magento\Framework\Registry')->register(
                'data_cart_rules',
                $model
            );

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('MW_RewardPoints::rewardpoints');
            $resultPage->getConfig()->getTitle()->prepend(__('Shopping Cart Reward Rules'));
            return $resultPage;
        } else {
            $this->messageManager->addError(__('Rule does not exist'));
            $this->_redirect('*/*/');
        }
    }
}
