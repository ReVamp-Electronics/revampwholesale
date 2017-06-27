<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Rma\Ui\DataProvider;

use \Aheadworks\Rma\Model\ResourceModel\Request\CollectionFactory;

/**
 * Class RmaDataProvider
 */
class RmaDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Rma collection
     *
     * @var \Aheadworks\Rma\Model\ResourceModel\Request\Collection
     */
    protected $collection;

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection
     */
    protected $customFieldCollection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection $customFieldCollection
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection $customFieldCollection,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->customFieldCollection = $customFieldCollection->setFilterForRmaGrid();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()
                ->joinCustomFieldValues($this->customFieldCollection)
                ->load();
            foreach ($this->getCollection() as $request) {
                $request->setData(
                    'products',
                    $request->getItemsCollection()->joinReason()->toArray(['product_id', 'name', 'reason'])
                );
            }
        }
        return parent::getData();
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $dbSelectFieldName = $filter->getField();
        if ( $dbSelectFieldName == 'id') {
            $dbSelectFieldName = 'main_table.id';
        }
        elseif (preg_match('/^cf[0-9]+_value$/', $dbSelectFieldName)) {
            $dbSelectFieldName = str_replace('_', '.', $dbSelectFieldName);
        }
        $this->getCollection()->addFieldToFilter(
            $dbSelectFieldName,
            [$filter->getConditionType() => $filter->getValue()]
        );
    }
}
