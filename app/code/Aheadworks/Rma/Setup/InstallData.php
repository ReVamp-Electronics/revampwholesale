<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Aheadworks\Rma\Model\Status\ConfigDefault
     */
    private $statusConfigDefault;

    /**
     * @var \Aheadworks\Rma\Model\Source\Request\Status
     */
    private $statusSource;

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\Status\CollectionFactory
     */
    private $statusCollectionFactory;

    /**
     * @var \Aheadworks\Rma\Model\CustomFieldFactory
     */
    private $customFieldFactory;

    /**
     * @var \Aheadworks\Rma\Model\CustomField\ConfigDefault
     */
    private $customFieldConfigDefault;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Aheadworks\Rma\Model\Status\ConfigDefault $statusConfigDefault
     * @param \Aheadworks\Rma\Model\Source\Request\Status $statusSource
     * @param \Aheadworks\Rma\Model\ResourceModel\Status\CollectionFactory $statusCollectionFactory
     * @param \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory
     * @param \Aheadworks\Rma\Model\CustomField\ConfigDefault $customFieldConfigDefault
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Aheadworks\Rma\Model\Status\ConfigDefault $statusConfigDefault,
        \Aheadworks\Rma\Model\Source\Request\Status $statusSource,
        \Aheadworks\Rma\Model\ResourceModel\Status\CollectionFactory $statusCollectionFactory,
        \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory,
        \Aheadworks\Rma\Model\CustomField\ConfigDefault $customFieldConfigDefault,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->statusConfigDefault = $statusConfigDefault;
        $this->statusSource = $statusSource;
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->customFieldFactory = $customFieldFactory;
        $this->customFieldConfigDefault = $customFieldConfigDefault;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->installStatuses($setup);
        $this->installCustomFields();
    }

    private function installStatuses(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        foreach ($this->statusConfigDefault->get() as $statusData) {
            $connection->insert(
                $setup->getTable('aw_rma_request_status'),
                [
                    'id' => $statusData['id'],
                    'name' => $this->statusSource->getOptionLabelByValue($statusData['id'], false),
                    'is_email_customer' => $statusData['is_email_customer'],
                    'is_email_admin' => $statusData['is_email_admin'],
                    'is_thread' => $statusData['is_thread']
                ]
            );
            foreach ($statusData['attribute'] as $attrCode => $attrValue) {
                foreach ($this->storeManager->getStores() as $store) {
                    $connection->insert(
                        $setup->getTable('aw_rma_status_attr_value'),
                        [
                            'store_id' => $store->getId(),
                            'attribute_code' => $attrCode,
                            'value' => $attrValue,
                            'status_id' => $statusData['id']
                        ]
                    );
                }
            }
        }
    }

    private function installCustomFields()
    {
        $websiteIds = [];
        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteIds[] = $website->getId();
        }
        $statusIds = $this->statusCollectionFactory->create()->getAllIds();
        foreach ($this->customFieldConfigDefault->get() as $customFieldData) {
            $customFieldData['is_system'] = 1;
            $customFieldData['website_ids'] = $websiteIds;

            $statusesRelatedFields = [
                'visible_for_status_ids',
                'editable_for_status_ids',
                'editable_admin_for_status_ids'
            ];
            foreach ($statusesRelatedFields as $field) {
                $fieldValue = $customFieldData[$field];
                if (is_array($fieldValue)) {
                    if (in_array('none', $fieldValue)) {
                        $customFieldData[$field] = [];
                    } elseif (in_array('all', $fieldValue)) {
                        $customFieldData[$field] = $statusIds;
                    }
                }
            }

            foreach ($customFieldData['attribute'] as $attrCode => $attrValue) {
                $perStoreAttrValues = [];
                foreach ($this->storeManager->getStores() as $store) {
                    $perStoreAttrValues[$store->getId()] = $attrValue;
                }
                $customFieldData['attribute'][$attrCode] = $perStoreAttrValues;
            }

            $counter = 0;
            $defaultValue = false;
            $perStoreOptionValues = ['value' => []];
            foreach ($customFieldData['option'] as $optionValue => $isDefault) {
                $perStoreOptionValues['value']['option_' . $counter] = [];
                foreach ($this->storeManager->getStores(true) as $store) {
                    $perStoreOptionValues['value']['option_' . $counter][$store->getId()] = $optionValue;
                }
                if ((int)$isDefault) {
                    $defaultValue = 'option_' . $counter;
                }
                $counter++;
            }
            $customFieldData['option'] = $perStoreOptionValues;
            if ($defaultValue) {
                $customFieldData['option']['default'] = [$defaultValue];
            }

            $this->customFieldFactory->create()
                ->setData($customFieldData)
                ->save()
            ;
        }
    }
}
