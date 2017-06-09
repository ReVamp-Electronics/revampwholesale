<?php

namespace MW\RewardPoints\Controller\Adminhtml\Spendcartrules;

class Edit extends \MW\RewardPoints\Controller\Adminhtml\Spendcartrules
{
    /**
     * Shopping Cart Spending Rule edit page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
    	$id    = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->get('MW\RewardPoints\Model\Spendcartrules')->load($id);

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
            $resultPage->getConfig()->getTitle()->prepend(__('Reward Points Spending Rule'));
            return $resultPage;
        } else {
            $this->messageManager->addError(__('Rule does not exist'));
            $this->_redirect('*/*/');
        }
    }
}
