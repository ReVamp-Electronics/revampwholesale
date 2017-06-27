<?php

namespace IWD\SalesRep\Model\ResourceModel\Order\Report;

use IWD\SalesRep\Model\ResourceModel\User as SalesrepResource;
use IWD\SalesRep\Model\User as Salesrep;
use IWD\SalesRep\Model\Order as SalesrepOrder;

/**
 * Class Collection
 * @package IWD\SalesRep\Model\ResourceModel\Order\Report
 */
class Collection extends \IWD\SalesRep\Model\ResourceModel\Order\Collection
{
    /**
     * @var bool
     */
    protected $_isTotals = false;

    /**
     * @var
     */
    private $sumColumns;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var \Magento\Directory\Model\Currency\DefaultLocator
     */
    private $currencyLocator;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Locale\CurrencyInterface $currencyInterface,
        \Magento\Directory\Model\Currency\DefaultLocator $defaultLocator,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->authSession = $authSession;
        $this->localeCurrency = $currencyInterface;
        $this->currencyLocator = $defaultLocator;
        $this->request = $requestInterface;
        $this->resourceConnection = $resourceConnection;
        
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _setTotalColumns()
    {
        $columns = $this->getSelect()->getPart('columns');
        foreach ($columns as &$column) {
            if (in_array($column[2], $this->sumColumns)) {
                $column[1] = new \Zend_Db_Expr('sum(' . $column[1] . ')');
            } else {
                $column[1] = new \Zend_Db_Expr('""');
            }
        }
        $this->getSelect()->setPart('columns', $columns);
    }

    public function setPageSize($size)
    {
        $this->_pageSize = false; // select all records for this report
        return $this;
    }
    
    protected function _initSelect()
    {
        $currencyCode = $this->currencyLocator->getDefaultCurrency($this->request);
        $typeSymbol = $this->localeCurrency->getCurrency($currencyCode)->getSymbol();

        parent::_initSelect();

        // filter by salesrep
        if ($this->authSession->getUser()->getData(\IWD\SalesRep\Model\Preference\ResourceModel\User\User::FIELD_NAME_SALESREPID)) {
            $this->addFieldToFilter('salesrep_id', $this->authSession->getUser()->getData(\IWD\SalesRep\Model\Preference\ResourceModel\User\User::FIELD_NAME_SALESREPID));
        }

        $orderFinalPrice = 'order.grand_total - ifnull(order.total_refunded, 0)';

        /**
         * @todo add EE store credits, reward points etc
         */

        $orderApplyWhenPrice = "if (commission_apply = 'after', $orderFinalPrice, $orderFinalPrice - order.discount_amount )";

        $commissionFixedFormula = "main_table.commission_rate";
        $commissionPercentFormula = "(main_table.commission_rate * $orderApplyWhenPrice) / 100";

        $commissionFormula = "if (
                main_table.commission_type = 'fixed',
                $commissionFixedFormula,
                $commissionPercentFormula) ";

        $this->join(
            ['salesrep_user' => $this->resourceConnection->getTableName(SalesrepResource::TABLE_NAME)],
            'main_table.salesrep_id = salesrep_user.' . Salesrep::SALESREP_ID,
            []
        )
        ->join(
            ['user' => $this->resourceConnection->getTableName('admin_user')],
            'salesrep_user.' . Salesrep::ADMIN_ID . ' = user.user_id',
            [
                'name' => new \Zend_Db_Expr('concat(user.firstname, " ", user.lastname)')
            ]
        )
        ->join(
            ['order' => $this->resourceConnection->getTableName('sales_order')],
            'main_table.order_id = order.entity_id',
            [
                'total' => 'order.grand_total',
                'invoiced' => 'order.total_invoiced',
                'refund' => 'order.total_refunded',
                'status' => 'order.status',
                'period' => 'order.created_at',
                'increment_id' => 'order.increment_id',
                'customer_name' => new \Zend_Db_Expr('concat(order.customer_firstname, " ", order.customer_lastname)'),
                'commission' => new \Zend_Db_Expr($commissionFormula),
                'commission_desc' => new \Zend_Db_Expr('if (commission_type = "fixed", concat(commission_rate, "' . $typeSymbol . '"), concat( trim(trailing "." from trim(trailing "0" from commission_rate)), "%", " ", commission_apply, " discounts"))')
            ]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _beforeLoad()
    {
        if ($this->_isTotals) {
            $this->_setTotalColumns();
        }
        return parent::_beforeLoad();
    }

    /**
     * @param $value
     * @return $this
     */
    public function isTotals($value)
    {
        $this->_isTotals = $value;
        return $this;
    }

    /**
     * @param $cols
     */
    public function setSumColumns($cols)
    {
        $this->sumColumns = $cols;
    }

    /**
     * @param array $statuses
     * @return $this|Collection
     */
    public function addOrderStatusFilter(array $statuses)
    {
        return $this->addFieldToFilter('status', [ 'in' => $statuses]);
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        switch ($field) {
            case Salesrep::SALESREP_ID:
                $this->getSelect()->where('main_table.' . SalesrepOrder::SALESREP_ID . ' = ' . $condition);
                break;
            default:
                return parent::addFieldToFilter($field, $condition);
        }

        return $this;
    }

    /**
     * @param $from
     * @param $to
     * @return $this
     */
    public function setDateRange($from, $to)
    {
        return $this
            ->addFieldToFilter('created_at', ['gteq' => $from])
            ->addFieldToFilter('created_at', ['lteq' => $to]);
    }
}
