<?php
namespace Evdpl\Jobopening\Model\ResourceModel;


class Jobopening extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('jobopening_jobopening', 'entity_id');
    }
}
