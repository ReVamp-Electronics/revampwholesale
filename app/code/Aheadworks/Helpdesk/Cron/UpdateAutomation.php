<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Cron;

/**
 * Class UpdateAutomation
 * @package Aheadworks\Helpdesk\Cron
 */
class UpdateAutomation
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
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

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
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Aheadworks\Helpdesk\Model\AutomationFactory $automationModelFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation\Recurring\CollectionFactory $recurringCollectionFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation $automationResource,
        \Aheadworks\Helpdesk\Model\Config $config,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->recurringCollectionFactory = $recurringCollectionFactory;
        $this->automationModelFactory = $automationModelFactory;
        $this->automationResource = $automationResource;
        $this->logger = $logger;
        $this->hduConfig = $config;
        $this->dateTime = $dateTime;
        return $this;
    }

    /**
     * Update recurring automation status
     * @return $this
     */
    public function execute()
    {
        if ($this->isLocked(
            $this->hduConfig->getParam(\Aheadworks\Helpdesk\Model\Config::LAST_EXEC_TIME_UPDATE_AUTOMATION),
            self::RUN_INTERVAL
        )) {
            return $this;
        }

        $runningEmailTasksCollection = $this->recurringCollectionFactory->create();
        $runningEmailTasksCollection->addFilter(
            'status',
            ['eq' => \Aheadworks\Helpdesk\Model\Automation\Recurring::RUNNING_STATUS],
            'public'
        )->addFilter(
            'action',
            [
                ['eq' => \Aheadworks\Helpdesk\Model\Source\Automation\Action::SEND_AGENT_EMAIL_VALUE],
                ['eq' => \Aheadworks\Helpdesk\Model\Source\Automation\Action::SEND_CUSTOMER_EMAIL_VALUE]
            ],
            'public'
        )->load();
        foreach ($runningEmailTasksCollection as $task) {
            try {
                $automationModel = $this->automationModelFactory->create();
                $this->automationResource->load($automationModel, $task->getAutomationId());
                if (!$automationModel->getId()) {
                    continue;
                }
                $validate = $automationModel->validateForTicket($task->getTicketId());
                if ($validate) {
                    //automation is validated. Don't create duplicates
                    continue;
                }
                $task->setStatus(\Aheadworks\Helpdesk\Model\Automation\Recurring::DONE_STATUS);
                $task->save();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        $this->setLastExecTime(\Aheadworks\Helpdesk\Model\Config::LAST_EXEC_TIME_UPDATE_AUTOMATION);
        return $this;
    }

    /**
     * Is locked
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

