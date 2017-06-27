<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml\Form\Element\Helper;

/**
 * Class Label
 * @package Aheadworks\Rma\Block\Adminhtml\Status\Edit\Form\Element\Helper
 */
class Label
{
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var \Magento\Store\Api\GroupRepositoryInterface
     */
    private $storeGroupRepository;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\GroupRepositoryInterface $storeGroupRepository
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\GroupRepositoryInterface $storeGroupRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->storeRepository = $storeRepository;
        $this->storeGroupRepository = $storeGroupRepository;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * Retrieves label html that corresponds to given $storeId
     *
     * @param int $storeId
     * @param array $attributes
     * @param string $default
     * @return string
     */
    public function getLabelHtml($storeId, $attributes = [], $default = '')
    {
        $labelData = $this->getLabelData($storeId);
        if ($labelData === null) {
            return $default;
        }
        return $this->wrapDataItem(
            $labelData['website'],
            [
                $this->wrapDataItem(
                    $labelData['storeGroup'],
                    [
                        $this->wrapDataItem($labelData['store'], [], ['class' => 'store'])
                    ],
                    ['class' => 'store-group']
                )
            ],
            array_merge($attributes, ['class' => 'website'])
        );
    }

    /**
     * @param int $storeId
     * @return array
     */
    private function getLabelData($storeId)
    {
        try {
            $store = $this->storeRepository->getById($storeId);
            $storeGroup = $this->storeGroupRepository->get($store->getStoreGroupId());
            $website = $this->websiteRepository->getById($store->getWebsiteId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            return null;
        }
        return [
            'website' => $website->getName(),
            'storeGroup' => $storeGroup->getName(),
            'store' => $store->getName()
        ];
    }

    /**
     * @param $item
     * @param array $childItems
     * @param array $attributes
     * @return string
     */
    private function wrapDataItem($item, $childItems = [], $attributes = [])
    {
        $attrObject = new \Magento\Framework\DataObject($attributes);
        foreach ($childItems as $childItem) {
            $childItem = '<li>' . $childItem . '</li>';
        }
        return '<ul ' . $attrObject->serialize() . '><li>' . htmlspecialchars($item, ENT_COMPAT) . '</li>' .
                implode('', $childItems) . '</ul>'
            ;
    }
}
