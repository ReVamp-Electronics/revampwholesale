<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Controller\Adminhtml\Relations;

/**
 * Currency Switcher SAVE controller
 */
class Save extends \Magento\Framework\App\Action\Action
{

    /**
     * Save Relations action
     *
     * @return void
     */
    public function execute()
    {
        $post = $this->getRequest()->getPost();
        $helperCurrency = $this->_objectManager->create('\MageWorx\CurrencySwitcher\Helper\Currency');
        $relationModel = $this->_objectManager->get('\MageWorx\CurrencySwitcher\Model\Relations');
        
        try {
            if (isset($post['currency_relation'])) {
                foreach ($post['currency_relation'] as $id => $relation) {
                    if (empty($relation['countries'])) {
                        continue;
                    }

                    if (isset($relation['countries']['use_default'])) {
                        $countries = $helperCurrency->getCountryByCurrency($relation['code']);
                    } else {
                        $countries = $relation['countries'];
                    }
                    if (is_array($countries)) {
                        $countries = implode(',', $countries);
                    }

                    $data = array(
                        'relation_id'   => $id,
                        'currency_code' => $relation['code'],
                        'countries'     => $countries
                    );
                    
                    $relationRow = $relationModel->load($id);
                    $relationRow->setData($data);
                    $relationRow->save();
                }

                $this->messageManager->addSuccessMessage(__('Currency relations were saved successfully.'));
            } else {
                throw new \Exception(__('No data to save'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
    }
}
