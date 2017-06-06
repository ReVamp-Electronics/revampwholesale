<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel;

/**
 * Class Automation
 * @package Aheadworks\Helpdesk\Model\ResourceModel
 */
class Automation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Serializable fields
     * @var array
     */
    protected $_serializableFields = ['conditions' => [[],[]], 'actions' => [[],[]]];

    /**
     * Automation collection factory
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Automation\CollectionFactory
     */
    protected $automationCollectionFactory;

    /**
     * Automation model factory
     * @var \Aheadworks\Helpdesk\Model\AutomationFactory
     */
    protected $automationFactory;

    /**
     * Recurring automation resource
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Automation\Recurring
     */
    protected $recurringAutomationResource;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Automation\CollectionFactory $collectionFactory
     * @param Automation\Recurring $recurringResource
     * @param \Aheadworks\Helpdesk\Model\AutomationFactory $automationFactory
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation\CollectionFactory $collectionFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation\Recurring $recurringResource,
        \Aheadworks\Helpdesk\Model\AutomationFactory $automationFactory,
        $connectionName = null
    ) {
        $this->automationCollectionFactory = $collectionFactory;
        $this->automationFactory = $automationFactory;
        $this->recurringAutomationResource = $recurringResource;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_helpdesk_automation', 'id');
    }

    /**
     * Create automation action
     *
     * @param $eventType
     * @param null $ticketId
     * @return $this
     */
    public function createAutomationAction($eventType, $ticketId = null, $messageId = null)
    {
        $automationCollection = $this->automationCollectionFactory->create();
        $automationCollection->addFilter(
            'status',
            ['eq' => \Aheadworks\Helpdesk\Model\Source\Automation\Status::ACTIVE_VALUE], 'public'
        );
        $automationCollection->addFilter('event', ['eq' => $eventType], 'public');
        $automationCollection->setOrder('priority', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $automationCollection->load();

        $processedTicketIds = [];
        foreach ($automationCollection->getItems() as $automation) {
            $automationModel = $this->automationFactory->create();
            $this->load($automationModel, $automation->getId());

            if (!$automationModel->getId()) {
                continue;
            }

            if ($ticketId) {
                $isValid = $automationModel->validateForTicket($ticketId);
                if (!$isValid) {
                    continue;
                }

                foreach ($automationModel->getActions() as $action) {
                    if ($eventType  != \Aheadworks\Helpdesk\Model\Source\Automation\Event::RECURRING_TASK_VALUE) {
                        $automationModel->runAction($action, $ticketId, $messageId);
                    } else {
                        $automationModel->scheduleAction($action, $ticketId, $messageId);
                    }
                }
                //one automation for one ticket
                break;
            } else {
                $ticketIds = $automationModel->getValidatedTicketIds();
                if(!$ticketIds) {
                    continue;
                }
                foreach ($automationModel->getActions() as $action) {
                    foreach($ticketIds as $ticketId) {
                        if (false !== array_search($ticketId, $processedTicketIds)) {
                            continue;
                        }
                        if ($eventType  != \Aheadworks\Helpdesk\Model\Source\Automation\Event::RECURRING_TASK_VALUE) {
                            $automationModel->runAction($action, $ticketId, $messageId);
                        } else {
                            $automationModel->scheduleAction($action, $ticketId, $messageId);
                        }
                    }
                }
                $processedTicketIds = array_merge($processedTicketIds, $ticketIds);
            }
        }
        return $this;
    }
}
