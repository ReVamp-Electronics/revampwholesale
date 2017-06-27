<?php

namespace MW\RewardPoints\Controller\Adminhtml\Cartrules;

class MassDelete extends \MW\RewardPoints\Controller\Adminhtml\Cartrules
{
	/**
	 * Delete many cart rules
	 *
	 * @return void
	 */
	public function execute()
    {
    	$ruleIds = $this->getRequest()->getParam('cart_rule_Grid');
        if (!is_array($ruleIds)) {
            $this->messageManager->addError(__('Please select rule(s)'));
        } else {
            try {
                foreach ($ruleIds as $ruleId) {
                    $rule = $this->_objectManager->get('MW\RewardPoints\Model\Cartrules')->load($ruleId);
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
