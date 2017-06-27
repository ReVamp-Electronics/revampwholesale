<?php

namespace MW\RewardPoints\Controller\Adminhtml\Spendcartrules;

class Save extends \MW\RewardPoints\Controller\Adminhtml\Spendcartrules
{
    /**
     * Save Shopping Cart Spending Rule
     */
    public function execute()
    {
    	if ($this->getRequest()->getPost()) {
            $data       = $this->getRequest()->getParams();
            $programId  = $this->getRequest()->getParam('id');
            $model      = $this->_objectManager->get('MW\RewardPoints\Model\Spendcartrules');
            $session    = $this->_objectManager->get('Magento\Backend\Model\Session');

            try {
                if (isset($data["store_view"])) {
                    if (in_array("0", $data["store_view"])) {
                        $storeView = '0';
                    } else {
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
                if ($programId != '') {
                    if ($storeManager->isSingleStoreMode()) {
                        $data['store_view'] = '0';
                    }

                    $model->setData($data)->setId($programId);
                    $model->save();

                    // Save conditions
                    if (isset($data['rule']['conditions'])) {
                        $data['conditions'] = $data['rule']['conditions'];
                    }
                    if (isset($data['rule']['actions'])) {
                        $data['actions'] = $data['rule']['actions'];
                    }
                    $model->load($programId);
                    unset($data['rule']);
                    $model->loadPost($data);
                    $model->save();
                }

                if ($programId == '') {
                    if ($storeManager->isSingleStoreMode()) {
                        $data['store_view'] = '0';
                    }

                    $model->setData($data)->save();

                    // Save conditions
                    if (isset($data['rule']['conditions'])) {
                        $data['conditions'] = $data['rule']['conditions'];
                    }
                    if (isset($data['rule']['actions'])) {
                        $data['actions'] = $data['rule']['actions'];
                    }
                    unset($data['rule']);
                    $model->loadPost($data);
                    $model->save();
                }

                $this->messageManager->addSuccess(__('The rule has successfully saved'));
                $session->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                    return;
                }

                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $session->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }

        $this->messageManager->addError(__('Unable to find rule to save'));
        $this->_redirect('*/*/');
    }
}
