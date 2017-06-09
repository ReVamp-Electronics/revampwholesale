<?php

namespace MW\RewardPoints\Controller\Adminhtml\Catalogrules;

class Save extends \MW\RewardPoints\Controller\Adminhtml\Catalogrules
{
    /**
     * Save Catalog Reward Rule
     */
    public function execute()
    {
    	if ($this->getRequest()->getPost()) {
            $data 		= $this->getRequest()->getParams();
            $ruleId 	= $this->getRequest()->getParam('id');
            $model  	= $this->_objectManager->get('MW\RewardPoints\Model\Catalogrules');
            $session    = $this->_objectManager->get('Magento\Backend\Model\Session');

            try {
                $storeView = '0';
                if (isset($data["store_view"])) {
                    if (!in_array("0", $data["store_view"])) {
                        $storeView = implode(",", $data["store_view"]);
                    }
                }
                $data["store_view"] = $storeView;

                if (!$data["reward_step"]) {
                    $data["reward_step"] = 0;
                }

                if ($data['rule_position'] == '') {
                    $data['rule_position'] = 0;
                }

                $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
                if ($ruleId != '') {
                    if ($storeManager->isSingleStoreMode()) {
                        $data['store_view'] = '0';
                    }

                    $model->setData($data)->setId($ruleId);
                    $model->save();

                    // Save conditions
                    if (isset($data['rule']['conditions'])) {
                        $data['conditions'] = $data['rule']['conditions'];
                    }
                    $model->load($ruleId);
                    unset($data['rule']);
                    $model->loadPost($data);
                    $model->save();
                }

                if ($ruleId == '') {
                    if ($storeManager->isSingleStoreMode()) {
                        $data['store_view'] = '0';
                    }

                    $model->setData($data)->save();

                    // Save conditions
                    if (isset($data['rule']['conditions'])) {
                        $data['conditions'] = $data['rule']['conditions'];
                    }
                    unset($data['rule']);
                    $model->loadPost($data);
                    $model->save();
                }

                if (!empty($data['auto_apply'])) {
                    $autoApply = true;
                    unset($data['auto_apply']);
                } else {
                    $autoApply = false;
                }

                $this->messageManager->addSuccess(__('The catalog reward rule has successfully saved'));
                $session->setFormData(false);

                if ($autoApply) {
                	$this->applyRules();
                    $this->messageManager->addSuccess(__('The catalog reward rules have been applied.'));
                    $this->_redirect('*/*/');
                    return;
                } else {
                	if ($this->getRequest()->getParam('back')) {
	                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);
	                    return;
	                }

	                $this->_redirect('*/*/');
	                return;
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $session->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }

        $this->messageManager->addError(__('Unable to find catalog rule to save'));
        $this->_redirect('*/*/');
    }
}
