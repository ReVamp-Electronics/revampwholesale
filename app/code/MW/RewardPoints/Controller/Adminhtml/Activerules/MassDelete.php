<?php

namespace MW\RewardPoints\Controller\Adminhtml\Activerules;

class MassDelete extends \MW\RewardPoints\Controller\Adminhtml\Activerules
{
	/**
	 * Delete many customer behavior rules
	 *
	 * @return void
	 */
	public function execute()
    {
    	$ruleIds = $this->getRequest()->getParam('activerules_grid');
        if (!is_array($ruleIds)) {
            $this->messageManager->addError(__('Please select rule(s)'));
        } else {
            try {
                foreach ($ruleIds as $ruleId) {
                    $rule = $this->_objectManager->get('MW\RewardPoints\Model\Activerules')->load($ruleId);
                    $rule->delete();
                }
                $this->messageManager->addSuccess(
                	'Total of %1 record(s) were successfully deleted', count($ruleIds)
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
