<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Model\Relation;

use Amasty\CustomerAttributes\Model\ResourceModel\Relation\Collection;
use Amasty\CustomerAttributes\Model\ResourceModel\Relation\CollectionFactory;
use Amasty\CustomerAttributes\Model\Relation;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Relation $relation */
        foreach ($items as $relation) {
            // load Relation Details
            $relation->getAttributeId();
            $relation->getAttributeOptions();
            $relation->getDependentAttributes();
            $this->loadedData[$relation->getId()] = $relation->getData();
        }

        $data = $this->dataPersistor->get('amasty_customer_attributes_relation');
        if (!empty($data)) {
            $relation = $this->collection->getNewEmptyItem();
            $relation->setData($data);
            $this->loadedData[$relation->getId()] = $relation->getData();
            $this->dataPersistor->clear('amasty_customer_attributes_relation');
        }

        return $this->loadedData;
    }
}
