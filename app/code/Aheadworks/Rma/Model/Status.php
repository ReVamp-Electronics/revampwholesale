<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model;

/**
 * Status Model
 *
 * @method string getName()
 * @method int getIsEmailCustomer()
 * @method int getIsEmailAdmin()
 * @method int getIsThread()
 * @method array getAttribute()
 *
 * @method \Aheadworks\Rma\Model\Status setName(string)
 * @method \Aheadworks\Rma\Model\Status setIsEmailCustomer(int)
 * @method \Aheadworks\Rma\Model\Status setIsEmailAdmin(int)
 * @method \Aheadworks\Rma\Model\Status setIsThread(int)
 * @method \Aheadworks\Rma\Model\Status setAttribute(array)
 */
class Status extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var int|null
     */
    protected $storeId = null;

    protected function _construct()
    {
        $this->_init('Aheadworks\Rma\Model\ResourceModel\Status');
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeId;
    }
}