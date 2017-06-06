<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\Gateway;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Api\DataObjectHelper;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentGatewayInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentGatewayInterfaceFactory;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\Gateway
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
     * @var DepartmentGatewayInterfaceFactory
     */
    private $departmentGatewayFactory;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param DataObjectHelper $dataObjectHelper
     * @param DepartmentGatewayInterfaceFactory $departmentGatewayFactory
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectHelper $dataObjectHelper,
        DepartmentGatewayInterfaceFactory $departmentGatewayFactory,
        EncryptorInterface $encryptor
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->departmentGatewayFactory = $departmentGatewayFactory;
        $this->encryptor = $encryptor;
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
                ->from($this->resourceConnection->getTableName('aw_helpdesk_department_gateway'), ['*'])
                ->where('department_id = :id');
            $gatewayData = $connection->fetchRow($select, ['id' => $entityId]);

            if ($gatewayData) {
                /** @var DepartmentGatewayInterface $gatewayDataObject */
                $gatewayDataObject = $this->departmentGatewayFactory->create();

                $gatewayData[DepartmentGatewayInterface::PASSWORD] = $this->encryptor->decrypt(
                    $gatewayData[DepartmentGatewayInterface::PASSWORD]
                );

                $this->dataObjectHelper->populateWithArray(
                    $gatewayDataObject,
                    $gatewayData,
                    DepartmentGatewayInterface::class
                );

                $entity->setGateway($gatewayDataObject);
            }
        }
        return $entity;
    }
}
