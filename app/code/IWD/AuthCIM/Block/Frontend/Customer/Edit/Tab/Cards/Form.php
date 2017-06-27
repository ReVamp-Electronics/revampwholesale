<?php

namespace IWD\AuthCIM\Block\Frontend\Customer\Edit\Tab\Cards;

use IWD\AuthCIM\Block\Customer\Edit\Tab\Cards\Form as GeneralForm;

/**
 * Class Form
 * @package IWD\AuthCIM\Block\Frontend\Customer\Edit\Tab\Cards
 */
class Form extends GeneralForm
{
    /**
     * @return \IWD\AuthCIM\Api\Data\CardInterface[]
     */
    public function getSavedCcList()
    {
        $customerId = $this->getCustomerId();
        return $this->getCardRepository()->getListForCustomer($customerId)->getItems();
    }
}
