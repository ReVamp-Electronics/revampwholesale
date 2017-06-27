<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\Source\Request;

/**
 * Class Status
 * @package Aheadworks\Rma\Model\Source\Request
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{
    const APPROVED                  = 1;
    const CANCELED                  = 2;
    const CLOSED                    = 3;
    const ISSUE_REFUND              = 4;
    const PACKAGE_RECEIVED          = 5;
    const PACKAGE_SENT              = 6;
    const PENDING_APPROVAL          = 7;

    const APPROVED_LABEL            = 'Approved';
    const CANCELED_LABEL            = 'Canceled';
    const CLOSED_LABEL              = 'Closed';
    const ISSUE_REFUND_LABEL        = 'Issue Refund';
    const PACKAGE_RECEIVED_LABEL    = 'Package Received';
    const PACKAGE_SENT_LABEL        = 'Package Sent';
    const PENDING_APPROVAL_LABEL    = 'Pending Approval';

    /**
     * @var null|array
     */
    protected $options = null;

    /**
     * @var null|array
     */
    protected $optionArray = null;

    public function getOptionsWithoutTranslation()
    {
        return [
            self::PENDING_APPROVAL  => self::PENDING_APPROVAL_LABEL,
            self::APPROVED          => self::APPROVED_LABEL,
            self::PACKAGE_SENT      => self::PACKAGE_SENT_LABEL,
            self::PACKAGE_RECEIVED  => self::PACKAGE_RECEIVED_LABEL,
            self::ISSUE_REFUND      => self::ISSUE_REFUND_LABEL,
            self::CLOSED            => self::CLOSED_LABEL,
            self::CANCELED          => self::CANCELED_LABEL
        ];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if ($this->options === null) {
            foreach ($this->getOptionsWithoutTranslation() as $value => $labelRaw) {
                $this->options[$value] = __($labelRaw);
            }
        }
        return $this->options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->optionArray === null) {
            $this->optionArray = [];
            foreach ($this->getOptions() as $value => $label) {
                $this->optionArray[] = ['value' => $value, 'label' => $label];
            }
        }
        return $this->optionArray;
    }

    /**
     * @param int $value
     * @param bool $translate
     * @return null|array
     */
    public function getOptionLabelByValue($value, $translate = true)
    {
        $options = $translate ? $this->getOptions() : $this->getOptionsWithoutTranslation();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }

    /**
     * @return array
     */
    public function getInactiveStatuses()
    {
        return [self::CLOSED, self::CANCELED];
    }

    /**
     * @return array
     */
    public function getActiveStatuses()
    {
        $statuses = array_keys($this->getOptions());
        return array_diff($statuses, $this->getInactiveStatuses());
    }
}
