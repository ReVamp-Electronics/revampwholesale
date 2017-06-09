<?php

namespace MW\RewardPoints\Controller\Adminhtml\Catalogrules;

class ApplyRules extends \MW\RewardPoints\Controller\Adminhtml\Catalogrules
{
    /**
     * Apply catalog reward rules
     */
    public function execute()
    {
    	try {
            $this->applyRules();
            $this->messageManager->addSuccess(
                __('The catalog reward rules have been applied.')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Unable to apply rules.'));
            throw $e;
        }

        $this->_redirect('*/*');
    }
}
