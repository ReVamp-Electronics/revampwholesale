<?php

namespace IWD\AuthCIM\Controller\Adminhtml\Customer\Cards;

/**
 * Class Delete
 * @package IWD\AuthCIM\Controller\Adminhtml\Customer\Cards
 */
class Delete extends AbstractAction
{
    /**
     * @return array
     */
    public function action()
    {
        $hash = $this->getCardHash();

        $this->getCardModel()->deletePaymentProfile($hash);

        return ['error' => false, 'status' => true];
    }
}
