<?php

namespace MW\RewardPoints\Controller\Adminhtml\Activerules;

use MW\RewardPoints\Model\Type;

class Save extends \MW\RewardPoints\Controller\Adminhtml\Activerules
{
    /**
     * Save Customer Behavior Reward Rule
     */
    public function execute()
    {
        if ($this->getRequest()->getPost()) {
            $data    = $this->getRequest()->getParams();
            $ruleId  = $this->getRequest()->getParam('id');
            $model   = $this->_objectManager->get('MW\RewardPoints\Model\Activerules');
            $session = $this->_objectManager->get('Magento\Backend\Model\Session');

            try {
                $couponCode       = "";
                $storeView        = "";
                $dateEvent        = "";
                $comment          = "";
                $customerGroupIds = "";
                $expiredDay       = 0;
                $defaultExpired   = 0;

                if (!isset($data['coupon_code'])) {
                    $data['coupon_code'] = $couponCode;
                }

                if ($data['type_of_transaction'] == Type::CUSTOM_RULE && $data['coupon_code'] != '') {
                    if ($ruleId) {
                        $activePoints = $model->getCollection()
                            ->addFieldToFilter('rule_id', ['eq' => $ruleId])
                            ->addFieldToFilter('coupon_code', $data['coupon_code']);
                    } else {
                        $activePoints = $model->getCollection()
                            ->addFieldToFilter('coupon_code', $data['coupon_code']);
                    }
                    if (sizeof($activePoints) > 0) {
                        $session->addError(__('The coupon code invalid'));
                        $session->setFormData($data);
                        $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                        return;
                    }
                }

                if (!isset($data['default_expired'])) {
                    $data['default_expired'] = $defaultExpired;
                }

                if (!isset($data['expired_day']) || $data['expired_day'] == '') {
                    $data['expired_day'] = $expiredDay;
                }

                if (!isset($data['date_event'])) {
                    $data['date_event'] = $dateEvent;
                }

                if (!isset($data['comment'])) {
                    $data['comment'] = $comment;
                }

                if (isset($data["customer_group_ids"])) {
                    $customerGroupIds = implode(",", $data["customer_group_ids"]);
                }
                $data["customer_group_ids"] = $customerGroupIds;

                if (isset($data["store_view"])) {
                    if (in_array("0", $data["store_view"])) {
                        $storeView = '0';
                    } else {
                        $storeView = implode(",", $data["store_view"]);
                    }
                }
                $data["store_view"] = $storeView;

                $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
                if ($storeManager->isSingleStoreMode()) {
                    $data['store_view'] = '0';
                }

                $model->setData($data)->setId($ruleId);
                $model->save();

                $this->messageManager->addSuccess(__('The rules has successfully saved'));
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

        $this->messageManager->addError(__('Unable to find rules to save'));
        $this->_redirect('*/*/');
    }
}
