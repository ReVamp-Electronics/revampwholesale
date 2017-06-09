<?php

namespace IWD\AuthCIM\Controller\Adminhtml\Customer\Cards;

use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Class Sync
 * @package IWD\AuthCIM\Controller\Adminhtml\Customer\Cards
 */
class Sync extends AbstractAction
{
    /**
     * @return array
     */
    public function action()
    {
        $customerProfileId = $this->getCustomerProfileId();
        $customerId = $this->getCustomerId();
        $this->getCoreRegistry()->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);

        if ($customerProfileId == 0) {
            $this->getCardModel()->removeCustomerProfile($customerId);
        } else {
            $this->getCardModel()->syncCustomerProfile($customerProfileId, $customerId);
        }

        return ['error' => false, 'status' => true, 'list_html' => $this->getResultHtml()];
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getResultHtml()
    {
        $resultPage = $this->getResultPageFactory()->create();

        $infoFormContainer = $resultPage->getLayout()->getBlock('iwd.authcim.edit.tab.cards.list');
        if (empty($infoFormContainer)) {
            throw new LocalizedException(__('Can not load block'));
        }

        return $infoFormContainer->toHtml();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getCustomerProfileId()
    {
        $hash = $this->getRequest()->getParam('profile_id', null);
        if ($hash == null) {
            throw new LocalizedException(__('Customer profile id is empty'));
        }

        return $hash;
    }
}
