<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class ReplyDate
 * @package Aheadworks\Helpdesk\Ui\Component\Listing\Columns
 */
class ReplyDate extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * Date format helper
     * @var \Aheadworks\Helpdesk\Helper\SmartDate
     */
    protected $smartDateHelper;

    /**
     * Timezone
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timezone;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Aheadworks\Helpdesk\Helper\SmartDate $smartDate
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Aheadworks\Helpdesk\Helper\SmartDate $smartDate,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        array $components = [],
        array $data = []
    ) {
        $this->smartDateHelper = $smartDate;
        $this->timezone = $timezone;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as &$item) {
            $item['last_reply_date'] = $this->prepareContent($item['last_reply_date']);
        }
        return $dataSource;
    }

    /**
     * Prepare content
     *
     * @param $customerId
     * @param $name
     * @return string
     */
    protected function prepareContent($date)
    {
        $format = 'Y-m-d H:i:s';
        $localDate = new \DateTime($date);
        $timezone = $this->timezone->getConfigTimezone();
        $timezoneClass = new \DateTimeZone($timezone);
        $localDate->setTimezone($timezoneClass);
        return $localDate->format($format);
    }
}