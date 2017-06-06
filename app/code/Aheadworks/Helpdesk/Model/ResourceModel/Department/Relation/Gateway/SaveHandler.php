<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\Gateway;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentGatewayInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class SaveHandler
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\Gateway
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
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param DataObjectProcessor $dataObjectProcessor
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectProcessor $dataObjectProcessor,
        EncryptorInterface $encryptor
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->encryptor = $encryptor;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getId();

        /** @var DepartmentGatewayInterface|null $gatewayDataObject */
        $gatewayDataObject = $entity->getGateway();
        if ($gatewayDataObject) {
            $gatewayDataObject->setDepartmentId($entityId);

            $connection = $this->getConnection();
            $tableName = $this->resourceConnection->getTableName('aw_helpdesk_department_gateway');

            if (!$gatewayDataObject->getIsEnabled()) {
                $connection->delete(
                    $tableName,
                    ['department_id = ?' => $entityId]
                );
            } else {
                $select = $connection->select()
                    ->from($tableName, ['*'])
                    ->where('email = :email')
                    ->where('is_enabled = 1');
                $anotherGatewayData = $connection->fetchRow($select, ['email' => $gatewayDataObject->getEmail()]);

                $duplicateEmailError = false;
                if (isset($anotherGatewayData[DepartmentGatewayInterface::ID])) {
                    if (!$gatewayDataObject->getId()) {
                        $duplicateEmailError = true;
                    } elseif ($gatewayDataObject->getId() != $anotherGatewayData[DepartmentGatewayInterface::ID]) {
                        $duplicateEmailError = true;
                    }

                    if ($duplicateEmailError) {
                        throw new LocalizedException(__('Gateway with the same email address already exists'));
                    }
                }

                $gatewayData = $this->dataObjectProcessor->buildOutputDataArray(
                    $gatewayDataObject,
                    DepartmentGatewayInterface::class
                );

                $gatewayData[DepartmentGatewayInterface::PASSWORD] = $this->encryptor->encrypt(
                    $gatewayData[DepartmentGatewayInterface::PASSWORD]
                );

                if ($gatewayDataObject->getId()) {
                    $connection->update($tableName, $gatewayData, ['id = ?' => (int)$gatewayDataObject->getId()]);
                } else {
                    $connection->insert($tableName, $gatewayData);
                }
            }
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
