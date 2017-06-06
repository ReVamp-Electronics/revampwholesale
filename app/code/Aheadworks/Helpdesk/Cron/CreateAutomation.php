<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Cron;

/**
 * Class CreateAutomation
 * @package Aheadworks\Helpdesk\Cron
 */
class CreateAutomation
{
    /**
     * Cron run interval.
     */
    const RUN_INTERVAL = 300;

    /**
     * Recurring collection factory
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Automation\Recurring\CollectionFactory
     */
    protected $recurringCollectionFactory;

    /**
     * Automation model factory
     * @var \Aheadworks\Helpdesk\Model\AutomationFactory
     */
    protected $automationModelFactory;

    /**
     * Automation resource model
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Automation
     */
    protected $automationResource;

    /**
     * DateTime lib
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Config model
     * @var \Aheadworks\Helpdesk\Model\Config
     */
    protected $hduConfig;

    /**
     * Constructor
     *
     * @param \Aheadworks\Helpdesk\Model\AutomationFactory $automationModelFactory
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Automation\Recurring\CollectionFactory $recurringCollectionFactory
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Automation $automationResource
     * @param \Aheadworks\Helpdesk\Model\Config $config
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Aheadworks\Helpdesk\Model\AutomationFactory $automationModelFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation\Recurring\CollectionFactory $recurringCollectionFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation $automationResource,
        \Aheadworks\Helpdesk\Model\Config $config,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->recurringCollectionFactory = $recurringCollectionFactory;
        $this->automationModelFactory = $automationModelFactory;
        $this->automationResource = $automationResource;
        $this->hduConfig = $config;
        $this->dateTime = $dateTime;
        return $this;
    }

    /**
     * Create recurring automation
     * @return $this
     */
    public function execute()
    {
        if ($this->isLocked(
            $this->hduConfig->getParam(\Aheadworks\Helpdesk\Model\Config::LAST_EXEC_TIME_CREATE_AUTOMATION),
            self::RUN_INTERVAL
        )) {
            return $this;
        }
        $this->automationResource->createAutomationAction(\Aheadworks\Helpdesk\Model\Source\Automation\Event::RECURRING_TASK_VALUE);
        $this->setLastExecTime(\Aheadworks\Helpdesk\Model\Config::LAST_EXEC_TIME_CREATE_AUTOMATION);
        return $this;
    }

    /**
     * Is cron locked
     * @param $paramName
     * @param $interval
     * @return bool
     */
    protected function isLocked($paramName, $interval)
    {
        $lastExecTime = $this->hduConfig->getParam($paramName);
        $now = $this->dateTime->timestamp();
        return $now < $lastExecTime + $interval;
    }

    /**
     * Set last exec time
     * @param $paramName
     */
    protected function setLastExecTime($paramName)
    {
        $now = $this->dateTime->timestamp();
        $this->hduConfig->setParam($paramName, $now);
    }
}

