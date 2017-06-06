<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\Website;

use Magento\Framework\App\ResourceConnection;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\Website
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
    public function __construct(MetadataPool $metadataPool, ResourceConnection $resourceConnection)
    {
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
        $websiteIds = $entity->getWebsiteIds();
        $websiteIdsOrig = $this->getWebsiteIds($entityId);

        $toInsert = array_diff($websiteIds, $websiteIdsOrig);
        $toDelete = array_diff($websiteIdsOrig, $websiteIds);

        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_helpdesk_department_website');

        if ($toInsert) {
            $data = [];
            foreach ($toInsert as $websiteId) {
                $data[] = [
                    'department_id' => (int)$entityId,
                    'website_id' => (int)$websiteId,
                ];
            }
            $connection->insertMultiple($tableName, $data);
        }
        if (count($toDelete)) {
            $connection->delete(
                $tableName,
                ['department_id = ?' => $entityId, 'website_id IN (?)' => $toDelete]
            );
        }
        return $entity;
    }

    /**
     * Get website IDs to which entity is assigned
     *
     * @param int $entityId
     * @return array
     */
    private function getWebsiteIds($entityId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('aw_helpdesk_department_website'), 'website_id')
            ->where('department_id = :id');
        return $connection->fetchCol($select, ['id' => $entityId]);
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
