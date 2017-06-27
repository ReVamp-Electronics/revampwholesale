<?php

namespace IWD\OrderManager\Model;

use Magento\Framework\Model\AbstractModel;

class Log extends AbstractModel
{
    /**
     * Initialization here
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('IWD\OrderManager\Model\ResourceModel\Log');
    }
}
