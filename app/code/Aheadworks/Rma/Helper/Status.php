<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Helper;

use Aheadworks\Rma\Model\Source\Request\Status as StatusSource;

/**
 * Class Status
 * @package Aheadworks\Rma\Helper
 */
class Status extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    private $statusesForCustomerCancel = [
        StatusSource::PENDING_APPROVAL,
        StatusSource::APPROVED,
        StatusSource::PACKAGE_RECEIVED,
        StatusSource::ISSUE_REFUND
    ];

    /**
     * @var array
     */
    private $statusesForPrintLabel = [
        StatusSource::APPROVED
    ];

    /**
     * @var array
     */
    private $statusesForConfirmShipping = [
        StatusSource::APPROVED
    ];

    /**
     * @var array
     */
    private $statusesForReplies = [
        StatusSource::PENDING_APPROVAL,
        StatusSource::APPROVED,
        StatusSource::PACKAGE_SENT,
        StatusSource::PACKAGE_RECEIVED,
        StatusSource::ISSUE_REFUND
    ];

    /**
     * @param int $statusId
     * @return bool
     */
    public function isAvailableForCustomerCancel($statusId)
    {
        return in_array($statusId, $this->statusesForCustomerCancel);
    }

    /**
     * @param int $statusId
     * @return bool
     */
    public function isAvailableForPrintLabel($statusId)
    {
        return in_array($statusId, $this->statusesForPrintLabel);
    }

    /**
     * @param int $statusId
     * @return bool
     */
    public function isAvailableForConfirmShipping($statusId)
    {
        return in_array($statusId, $this->statusesForConfirmShipping);
    }

    /**
     * @param int $statusId
     * @return bool
     */
    public function isAvailableForReply($statusId)
    {
        return in_array($statusId, $this->statusesForReplies);
    }

    /**
     * @param $statusId
     * @param $statusIdOld
     * @return bool
     */
    public function isAvailableForCustomer($statusId, $statusIdOld)
    {
        switch ($statusId) {
            case StatusSource::PACKAGE_SENT:
                return $this->isAvailableForConfirmShipping($statusIdOld);
            case StatusSource::CANCELED:
                return $this->isAvailableForCustomerCancel($statusIdOld);
        }
        return true;
    }
}