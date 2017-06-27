<?php

namespace MW\RewardPoints\Controller\Adminhtml\Cartrules;

class MassStatus extends \MW\RewardPoints\Controller\Adminhtml\Cartrules
{
	/**
	 * Change status of many cart rules
	 *
	 * @return void
	 */
	public function execute()
    {
    	$ruleIds = $this->getRequest()->getParam('cart_rule_Grid');
        if (!is_array($ruleIds)) {
            $this->messageManager->addError(__('Please select rule(s)'));
        } else {
            $status = $this->getRequest()->getParam('status');

            try {
                foreach ($ruleIds as $ruleId) {
                    $rule = $this->_objectManager->get('MW\RewardPoints\Model\Cartrules')->load($ruleId);
                    $rule->setStatus($status);
                    $rule->save();
                    unset($rule);
                }
                $this->messageManager->addSuccess(
                	'Total of %1 record(s) were successfully updated', count($ruleIds)
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
