<?php

namespace MW\RewardPoints\Controller\Adminhtml\Activerules;

class MassStatus extends \MW\RewardPoints\Controller\Adminhtml\Activerules
{
	/**
	 * Change status of many customer behavior rules
	 *
	 * @return void
	 */
	public function execute()
    {
    	$ruleIds = $this->getRequest()->getParam('activerules_grid');
        if (!is_array($ruleIds)) {
            $this->messageManager->addError(__('Please select rule(s)'));
        } else {
            $status = $this->getRequest()->getParam('status');

            try {
                foreach ($ruleIds as $ruleId) {
                    $rule = $this->_objectManager->get('MW\RewardPoints\Model\Activerules')->load($ruleId);
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
