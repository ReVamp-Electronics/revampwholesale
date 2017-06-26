<?php
/**
 * Jobopening Resource Collection
 */
namespace Evdpl\Jobopening\Model\ResourceModel\Jobopening;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Evdpl\Jobopening\Model\Jobopening', 'Evdpl\Jobopening\Model\ResourceModel\Jobopening');
    }
}
