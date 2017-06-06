<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Helper;

use Magento\Store\Model\ScopeInterface;
use Aheadworks\Rma\Model\Source\Config\Cms\Block as BlockConfig;

/**
 * Class CmsBlock
 * @package Aheadworks\Rma\Helper
 */
class CmsBlock extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $cmsBlockFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $cmsFilterProvider;

    /**
     * @var array
     */
    private $cmsBlockHtml = [];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Cms\Model\BlockFactory $cmsBlockFactory
     * @param \Magento\Cms\Model\Template\FilterProvider $cmsFilterProvider
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\BlockFactory $cmsBlockFactory,
        \Magento\Cms\Model\Template\FilterProvider $cmsFilterProvider
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->cmsBlockFactory = $cmsBlockFactory;
        $this->cmsFilterProvider = $cmsFilterProvider;
    }

    /**
     * @param string $pathConfig
     * @return string
     */
    public function getBlockHtml($pathConfig)
    {
        if (!isset($this->cmsBlockHtml[$pathConfig])) {
            $cmsBlockHtml = '';
            $cmsBlockId = $this->scopeConfig->getValue($pathConfig, ScopeInterface::SCOPE_STORE);
            if ($cmsBlockId && $cmsBlockId != BlockConfig::DONT_DISPLAY) {
                $storeId = $this->storeManager->getStore()->getId();
                $cmsBlock = $this->cmsBlockFactory->create()
                    ->setStoreId($storeId)
                    ->load($cmsBlockId)
                ;
                if ($cmsBlock->isActive()) {
                    $cmsBlockHtml = $this->cmsFilterProvider->getBlockFilter()
                        ->setStoreId($storeId)
                        ->filter($cmsBlock->getContent())
                        ;
                }
            }
            $this->cmsBlockHtml[$pathConfig] = $cmsBlockHtml;
        }
        return $this->cmsBlockHtml[$pathConfig];
    }
}