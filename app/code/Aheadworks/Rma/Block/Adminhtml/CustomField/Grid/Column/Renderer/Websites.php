<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml\CustomField\Grid\Column\Renderer;

/**
 * Class Websites
 * @package Aheadworks\Rma\Block\Adminhtml\CustomField\Grid\Column\Renderer
 */
class Websites extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        array $data = []
    ) {
        $this->websiteRepository = $websiteRepository;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $websiteNames = [];
        try {
            foreach ($row->getWebsiteIds() as $websiteId) {
                $websiteNames[] = $this->websiteRepository->getById($websiteId)->getName();
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            return '';
        }
        return implode(', ', $websiteNames);
    }
}
