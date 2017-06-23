<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Plugin;

use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;

class RefreshCollection
{

    protected $loaded = 0;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var FlagFactory
     */
    protected $_reportsFlagFactory;

    /**
     * RefreshCollection constructor.
     * @param TimezoneInterface $localeDate
     * @param FlagFactory $reportsFlagFactory
     */
    public function __construct(
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory
    ) {
        $this->_localeDate = $localeDate;
        $this->_reportsFlagFactory = $reportsFlagFactory;
    }

    /**
     * Get if updated
     *
     * @param string $reportCode
     * @return string
     */
    protected function _getUpdatedAt($reportCode)
    {
        $flag = $this->_reportsFlagFactory->create()->setReportFlagCode($reportCode)->loadSelf();
        return $flag->hasData() ? $flag->getLastUpdate() : '';
    }

    /**
     * @param $subject
     * @param \Closure $closure
     * @param $printQuery
     * @param $logQuery
     * @return mixed
     */
    public function aroundLoadData(
        $subject,
        \Closure $closure,
        $printQuery,
        $logQuery
    ) {
        $value = [
            'id' => 'amasty_reports_customers_customers',
            'report' => __('Amasty Customers Report'),
            'comment' => __('Amasty Customers Report'),
            'updated_at' => $this->_getUpdatedAt('amasty_reports_customers_customers')
        ];
        $item = new DataObject();
        $item->setData($value);
        $result = $closure($printQuery, $logQuery);
        if (!$this->loaded) {

            $result->addItem($item);
            $this->loaded = 1;
        }
        return $result;
    }

}
