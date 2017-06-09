<?php

namespace IWD\SalesRep\Model\Plugin\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as UiComponentDataProvider;
use IWD\SalesRep\Helper\Data as SalesrepHelper;

/**
 * Class DataProvider
 * @package IWD\SalesRep\Model\Plugin\Framework\View\Element\UiComponent\DataProvider
 */
class DataProvider
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory
     */
    private $salesrepAttachedCustomerCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * DataProvider constructor.
     * @param \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $salesrepAttachedCustomerCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $salesrepAttachedCustomerCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->authSession = $authSession;
        $this->scopeConfig = $scopeConfig;
        $this->salesrepAttachedCustomerCollectionFactory = $salesrepAttachedCustomerCollectionFactory;
        $this->resource = $resourceConnection;
    }

    /**
     * @param UiComponentDataProvider $subject
     * @param \Magento\Framework\Api\Filter $filter
     */
    public function beforeAddFilter(UiComponentDataProvider $subject, \Magento\Framework\Api\Filter $filter)
    {
        $field = $filter->getField();
        $field = (strpos($field, 'main_table') === false) ? 'main_table.' . $field : $field;
        $filter->setField($field);
    }

    public function afterGetSearchResult(UiComponentDataProvider $subject, SearchResultInterface $searchResult)
    {
        // show all orders no matter of current admin user
        if (!$this->scopeConfig->getValue(SalesrepHelper::XML_PATH_SHOW_ONLY_ASSIGNED_ORDERS)) {
            return $searchResult;
        }

        $user = $this->authSession->getUser();
        $salesrepId = $user->getData(\IWD\SalesRep\Model\Preference\ResourceModel\User\User::FIELD_NAME_SALESREPID);
        // current admin user is nor sales rep
        if ($salesrepId === null) {
            return $searchResult;
        }

        $customerIds = $this->getAssignedCustomerIds($salesrepId);
        switch ($subject->getName()) {
            case 'sales_order_grid_data_source':
                /**
                 * @var $searchResult \Magento\Framework\Data\Collection\AbstractDb
                 */
                $searchResult->addFieldToFilter('main_table.customer_id', ['in' => $customerIds]);
                break;
            case 'sales_order_invoice_grid_data_source':
            case 'sales_order_shipment_grid_data_source':
            case 'sales_order_creditmemo_grid_data_source':
                /**
                 * @var $searchResult \Magento\Framework\Data\Collection\AbstractDb
                 */
                $searchResult->getSelect()->joinInner(
                    ['order' => $this->resource->getTableName('sales_order')],
                    'main_table.order_id = order.entity_id',
                    ['customer_id']
                );
                $searchResult->addFieldToFilter('customer_id', ['in' => $customerIds]);

                break;
        }

        return $searchResult;
    }

    public function getAssignedCustomerIds($salesrepId)
    {
        $ids = [];
        $collection = $this->salesrepAttachedCustomerCollectionFactory->create()
            ->addFieldToFilter('salesrep_id', $salesrepId);
        foreach ($collection->getItems() as $item) {
            $ids[] = $item->getData('customer_id');
        }

        return $ids;
    }
}
