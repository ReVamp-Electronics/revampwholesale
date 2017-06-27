<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

namespace Amasty\Reports\Model\ResourceModel\Customers\Returning;

use Magento\Customer\Api\GroupManagementInterface;
use Amasty\Reports\Traits\Filters;

class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
{
    use Filters;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Amasty\Reports\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig,
        \Magento\Framework\App\RequestInterface $request, // TODO move it out of here
        \Amasty\Reports\Helper\Data $helper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        $modelName = self::CUSTOMER_MODEL_NAME
    ) {
        $this->_fieldsetConfig = $fieldsetConfig;
        $this->_modelName = $modelName;
        $this->request = $request;
        $this->helper = $helper;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $entitySnapshot,
            $fieldsetConfig,
            $connection,
            $modelName
        );
    }

    public function prepareCollection($collection, $tablePrefix = 'main_table')
    {
        $this->applyBaseFilters($collection, $tablePrefix);
        $this->applyToolbarFilters($collection, $tablePrefix);


        foreach ($collection->getItems() as $item) {
            $item->setData('returning_customers', $item->getData('count') - $item->getData('new_customers'));
            $percent = 100;
            if ($item->getData('new_customers')) {
                $percent = $item->getData('returning_customers') / $item->getData('count') * 100;
            }
            $item->setData('percent', $percent);
        }
    }
    
    public function applyBaseFilters($collection, $tablePrefix = 'main_table')
    {
        $collection->getSelect()
            ->reset(\Zend_Db_Select::FROM);

        $collection->getSelect()->from(['main_table' => $this->getTable('sales_order')]);

        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS);

        $subQuery = new \Zend_Db_Expr(
            "COUNT(distinct customer_id) -
       (SELECT COUNT(distinct customer_id) FROM sales_order WHERE FIND_IN_SET(customer_id, GROUP_CONCAT(customerId)) AND sales_order.created_at < period)"
        );

        $collection->getSelect()
            ->columns([
                'customerId' => 'customer_id',
                'count' => 'COUNT(entity_id)',
                'period' => 'DATE(created_at)',
                'new_customers' => '('.$subQuery.')',
                'entity_id' => 'CONCAT(entity_id,\''.$this->createUniqueEntity().'\')'
                //'returning_customers' => 'COUNT(entity_id) - new_customers'
            ])
        ;

        $collection->getSelect()
            ->group("DATE(created_at)")
        ;
    }

    public function applyToolbarFilters($collection, $tablePrefix = 'main_table')
    {
        $this->addFromFilter($collection, $tablePrefix);
        $this->addToFilter($collection, $tablePrefix);
        $this->addInterval($collection, 'DATE(created_at)');
    }
    public function addInterval($collection, $dateFiled = 'created_at')
    {
        $filters = $this->request->getParam('amreports');
        $interval = isset($filters['interval']) ? $filters['interval'] : 'day';
        $collection->getSelect()
            ->reset(\Zend_Db_Select::GROUP);
        switch ($interval) {
            case 'year';
                $collection->getSelect()
                    ->columns([
                        'period' => "YEAR($dateFiled)",
                    ])
                    ->group("YEAR($dateFiled)")
                ;
                break;
            case 'month';
                $collection->getSelect()
                    ->columns([
                        'period' => "CONCAT(YEAR($dateFiled), '-', MONTH($dateFiled))",
                    ])
                    ->group("MONTH($dateFiled)")
                ;
                break;
            case 'week';
                $collection->getSelect()
                    ->columns([
                        'period' => "CONCAT(ADDDATE(DATE($dateFiled), INTERVAL 1-DAYOFWEEK($dateFiled) DAY), ' - ', ADDDATE(DATE($dateFiled), INTERVAL 7-DAYOFWEEK($dateFiled) DAY))",
                    ])
                    ->group("WEEK($dateFiled)")
                ;
                break;
            case 'day':
            default:
                $collection->getSelect()
                    ->columns([
                        'period' => "DATE($dateFiled)",
                    ])
                    ->group('DATE('.$dateFiled.')')
                ;
        }
    }

    public function addGroupBy($collection, $tablePrefix)
    {
        $collection->getSelect()
            ->group("DATE($tablePrefix.created_at)")
        ;
    }

    public function addFromFilter($collection)
    {
        $filters = $this->request->getParam('amreports');
        $from = isset($filters['from']) ? $filters['from'] : date('Y-m-d', $this->helper->getDefaultFromDate());
        if ($from) {
            $collection->getSelect()->where('DATE(created_at) >= ?', $from);
        }
    }

    public function addToFilter($collection)
    {
        $filters = $this->request->getParam('amreports');
        $to = isset($filters['to']) ? $filters['to'] : date('Y-m-d');
        if ($to) {
            $collection->getSelect()->where('DATE(created_at) <= ?', $to);
        }
    }
}
