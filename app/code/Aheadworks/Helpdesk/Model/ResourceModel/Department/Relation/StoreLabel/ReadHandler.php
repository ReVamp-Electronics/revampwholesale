<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\StoreLabel;

use Magento\Framework\App\ResourceConnection;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Api\DataObjectHelper;
use Aheadworks\Helpdesk\Api\Data\DepartmentStoreLabelInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentStoreLabelInterfaceFactory;

/**
 * Class ReadHandler
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\StoreLabel
 * @codeCoverageIgnore
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DepartmentStoreLabelInterfaceFactory
     */
    private $departmentStoreLabelFactory;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param DataObjectHelper $dataObjectHelper
     * @param DepartmentStoreLabelInterfaceFactory $departmentStoreLabelFactory
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectHelper $dataObjectHelper,
        DepartmentStoreLabelInterfaceFactory $departmentStoreLabelFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->departmentStoreLabelFactory = $departmentStoreLabelFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entityId = (int)$entity->getId()) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(DepartmentInterface::class)->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_helpdesk_department_label'), ['store_id', 'label'])
                ->where('department_id = :id');
            $storelabels = $connection->fetchAll($select, ['id' => $entityId]);

            $storeLabelsObjects = [];
            foreach ($storelabels as $storeLabelData) {
                /** @var DepartmentStoreLabelInterface $departmentDataObject */
                $departmentDataObject = $this->departmentStoreLabelFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $departmentDataObject,
                    $storeLabelData,
                    DepartmentStoreLabelInterface::class
                );
                $storeLabelsObjects[] = $departmentDataObject;
            }
            $entity->setStoreLabels($storeLabelsObjects);
        }
        return $entity;
    }
}
