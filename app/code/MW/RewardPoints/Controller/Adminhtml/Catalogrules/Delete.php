<?php

namespace MW\RewardPoints\Controller\Adminhtml\Catalogrules;

class Delete extends \MW\RewardPoints\Controller\Adminhtml\Catalogrules
{
	/**
	 * Delete a catalog reward rule
	 */
	public function execute()
    {
    	$ruleId = $this->getRequest()->getParam('id');
    	if ($ruleId > 0) {
            try {
                $model = $this->_objectManager->get('MW\RewardPoints\Model\Catalogrules')->load($ruleId);
                $model->delete();
                $this->messageManager->addSuccess(__('The catalog rule has successfully deleted'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['id' => $ruleId]);
            }
        }

        $this->_redirect('*/*/');
    }
}
