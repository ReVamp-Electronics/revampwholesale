<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    )
    {
        parent::__construct($context);
        $this->session = $session;
        $this->localeDate = $localeDate;
    }

    public function getDefaultFromDate()
    {
        return strtotime('-7 day');
    }

    public function getDefaultToDate()
    {
        return time();
    }

    public function getCurrentStoreId()
    {
        return $this->session->getAmreportsStore();
    }

    public function setCurrentStore($store)
    {
        return $this->session->setAmreportsStore($store);
    }

    public function convertTime($date)
    {
        $formatTime = $this->localeDate->date($date);
        return $formatTime->format('Y-m-d');
    }
}
