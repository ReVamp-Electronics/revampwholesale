<?php

namespace IWD\OrderManager\Model\Rewrite\Session;

use Magento\Backend\Model\Session\Quote as SessionQuote;

class Quote extends SessionQuote
{
    /**
     * Clear Quote Params
     *
     * @return void
     */
    public function clearQuoteParams()
    {
        $this->_quote = null;
        $this->_store = null;
    }
}
