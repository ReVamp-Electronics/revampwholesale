<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Helper;

/**
 * Class SmartDate
 * @package Aheadworks\Helpdesk\Helper
 */
class SmartDate extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DATE_FORMAT_DEFAULT       = 'Y, d M, H:i';
    const DATE_FORMAT_THIS_YEAR     = 'd M, H:i';
    const DATE_FORMAT_TODAY         = "H:i";
    const DATE_FORMAT_YESTERDAY     = "H:i";

    /**
     * Timezone
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timezone;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone
    ) {
        $this->timezone = $timezone;
        parent::__construct($context);
        return $this;
    }
    /**
     * Get date format
     * @param $date string
     * @return string
     */
    public function getSmartDate($date)
    {
        $localDate = new \DateTime($date);
        $currentDate = new \DateTime();
        $yesterdayDate = clone $currentDate;
        $yesterdayDate->modify('-1 day');
        $timezone = $this->timezone->getConfigTimezone();
        $timezoneClass = new \DateTimeZone($timezone);
        $localDate->setTimezone($timezoneClass);
        $currentDate->setTimezone($timezoneClass);
        $yesterdayDate->setTimezone($timezoneClass);

        if ($localDate->format('Y-m-d') == $currentDate->format('Y-m-d')) {
            $result = 'Today, ' . $localDate->format(self::DATE_FORMAT_TODAY);
        } elseif ($localDate->format('Y-m-d') == $yesterdayDate->format('Y-m-d')) {
            $result = 'Yesterday, ' . $localDate->format(self::DATE_FORMAT_YESTERDAY);
        } elseif ($localDate->format('Y') == $currentDate->format('Y')) {
            $result = $localDate->format(self::DATE_FORMAT_THIS_YEAR);
        } else {
            $result = $localDate->format(self::DATE_FORMAT_DEFAULT);
        }
        return $result;
    }
}