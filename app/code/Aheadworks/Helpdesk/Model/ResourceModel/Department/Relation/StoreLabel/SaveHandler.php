<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\StoreLabel;

use Magento\Framework\App\ResourceConnection;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentStoreLabelInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SaveHandler
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\StoreLabel
 * @codeCoverageIgnore
 */
class SaveHandler implements ExtensionInterface
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
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getId();

        $storeLabels = $entity->getStoreLabels();

        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_helpdesk_department_label');

        $connection->delete($tableName, ['department_id = ?' => (int)$entityId]);

        if ($storeLabels) {
            $data = [];
            $storeIds = [];
            /** @var DepartmentStoreLabelInterface $storeLabel */
            foreach ($storeLabels as $storeLabel) {
                if (isset($storeIds[$storeLabel->getStoreId()])) {
                    throw new LocalizedException(__('More than one label per store view is not allowed'));
                } else {
                    $storeIds[$storeLabel->getStoreId()] = 1;
                }
                $data[] = [
                    'department_id' => (int)$entityId,
                    DepartmentStoreLabelInterface::STORE_ID => $storeLabel->getStoreId(),
                    DepartmentStoreLabelInterface::LABEL    => $storeLabel->getLabel(),
                ];
            }
            $connection->insertMultiple($tableName, $data);
        }

        return $entity;
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \Exception
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(DepartmentInterface::class)->getEntityConnectionName()
        );
    }
}
