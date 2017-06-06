<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel\Department\Observer;

use Magento\Framework\Event\ObserverInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\CollectionFactory as DepartmentCollectionFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\Collection as DepartmentCollection;

/**
 * Class SaveBefore
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Department\Observer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveBefore implements ObserverInterface
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
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var DepartmentInterfaceFactory
     */
    private $departmentInterfaceFactory;

    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepositoryInterface;

    /**
     * @var DepartmentCollectionFactory
     */
    private $departmentCollectionFactory;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DepartmentInterfaceFactory $departmentInterfaceFactory
     * @param DepartmentRepositoryInterface $departmentRepositoryInterface
     * @param DepartmentCollectionFactory $departmentCollectionFactory
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        DepartmentInterfaceFactory $departmentInterfaceFactory,
        DepartmentRepositoryInterface $departmentRepositoryInterface,
        DepartmentCollectionFactory $departmentCollectionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->departmentInterfaceFactory = $departmentInterfaceFactory;
        $this->departmentRepositoryInterface = $departmentRepositoryInterface;
        $this->departmentCollectionFactory = $departmentCollectionFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var DepartmentInterface $department */
        $department = $observer->getData('entity');
        $departmentOrig = $this->getOriginalDepartment($department->getId());

        if ($departmentOrig->getId()) {
            if ($departmentOrig->getIsDefault()) {
                $websiteIds = $department->getWebsiteIds();
                if (!$department->getIsDefault() && count($websiteIds) > 1) {
                    throw new LocalizedException(
                        __('Default department can not be changed. Please select a new one first.')
                    );
                }
                $websites = $department->getWebsiteIds();
                $websitesOrig = $departmentOrig->getWebsiteIds();
                $websitesRemoved = array_diff($websitesOrig, $websites);
                if (count($websitesRemoved) > 0) {
                    throw new LocalizedException(__(
                        'Websites can not be removed from default department.' .
                        ' Please select new default department for these websites.'
                    ));
                }
            }
        }
        if ($department->getIsDefault()) {
            if ($department->getIsEnabled()) {
                foreach ($department->getWebsiteIds() as $websiteId) {
                    try {
                        $defaultDepartment = $this->departmentRepositoryInterface->getDefaultByWebsiteId($websiteId);
                        if (count($defaultDepartment->getWebsiteIds()) > 1) {
                            $depWebsiteIds = $defaultDepartment->getWebsiteIds();
                            foreach ($depWebsiteIds as $key => $value) {
                                if ($value == $websiteId) {
                                    $this->unsetWebsite($defaultDepartment, $websiteId);
                                    break;
                                }
                            }
                        } else {
                            $this->resetIsDefault($defaultDepartment);
                        }
                    } catch (LocalizedException $e) {

                    }
                }
            } else {
                throw new LocalizedException(
                    __('Default department can not be disabled')
                );
            }
        }
    }

    /**
     * Get original department
     *
     * @param int $entityId
     * @return DepartmentInterface
     */
    private function getOriginalDepartment($entityId)
    {
        /** @var DepartmentCollection $collection */
        $collection = $this->departmentCollectionFactory->create();
        $collection->addFieldToFilter(DepartmentInterface::ID, $entityId);
        $departmentData = null;
        foreach ($collection->getItems() as $item) {
            $departmentData = $item->getData();
            break;
        }

        /** @var DepartmentInterface $depatmentDataObject */
        $depatmentDataObject = $this->departmentInterfaceFactory->create();

        if ($departmentData) {
            $this->dataObjectHelper->populateWithArray(
                $depatmentDataObject,
                $departmentData,
                DepartmentInterface::class
            );
        }

        return $depatmentDataObject;
    }

    /**
     * Unset website from department specified
     *
     * @param DepartmentInterface $department
     * @param int $websiteId
     * @return void
     */
    private function unsetWebsite($department, $websiteId)
    {
        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_helpdesk_department_website');
        $connection->delete(
            $tableName,
            ['department_id = ?' => $department->getId(), 'website_id IN (?)' => $websiteId]
        );
    }

    /**
     * Update department
     *
     * @param DepartmentInterface $department
     * @return void
     */
    private function resetIsDefault($department)
    {
        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_helpdesk_department');

        $department->setIsDefault(false);
        $departmentData = $this->dataObjectProcessor->buildOutputDataArray(
            $department,
            DepartmentInterface::class
        );
        unset($departmentData[DepartmentInterface::WEBSITE_IDS]);
        unset($departmentData[DepartmentInterface::STORE_LABELS]);
        unset($departmentData[DepartmentInterface::GATEWAY]);
        unset($departmentData[DepartmentInterface::PERMISSIONS]);

        $connection->update($tableName, $departmentData, ['id = ?' => $department->getId()]);
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
