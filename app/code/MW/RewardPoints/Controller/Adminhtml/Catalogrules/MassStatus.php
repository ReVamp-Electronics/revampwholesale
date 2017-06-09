<?php

namespace MW\RewardPoints\Controller\Adminhtml\Catalogrules;

class MassStatus extends \MW\RewardPoints\Controller\Adminhtml\Catalogrules
{
	/**
	 * Change status of many catalog rules
	 *
	 * @return void
	 */
	public function execute()
    {
    	$ruleIds = $this->getRequest()->getParam('catalog_rules_grid');
        if (!is_array($ruleIds)) {
            $this->messageManager->addError(__('Please select catalog rule(s)'));
        } else {
            $status = $this->getRequest()->getParam('status');

            try {
                foreach ($ruleIds as $ruleId) {
                    $rule = $this->_objectManager->get('MW\RewardPoints\Model\Catalogrules')->load($ruleId);
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
