<?php

namespace MW\RewardPoints\Controller\Adminhtml\Spendcartrules;

class Delete extends \MW\RewardPoints\Controller\Adminhtml\Spendcartrules
{
	/**
	 * Delete a spending rule
	 */
	public function execute()
    {
    	$ruleId = $this->getRequest()->getParam('id');
    	if ($ruleId > 0) {
            try {
                $model = $this->_objectManager->get('MW\RewardPoints\Model\Spendcartrules')->load($ruleId);
                $model->delete();
                $this->messageManager->addSuccess(__('The rule has successfully deleted'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['id' => $ruleId]);
            }
        }

        $this->_redirect('*/*/');
    }
}
