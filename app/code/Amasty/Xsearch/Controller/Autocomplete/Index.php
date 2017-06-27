<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */

namespace Amasty\Xsearch\Controller\Autocomplete;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;
use Amasty\Xsearch\Controller\RegistryConstants;

class Index extends \Amasty\Xsearch\Controller\Autocomplete
{
    protected $_helper;
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Xsearch\Helper\Data $helper,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Url\Helper\Data $urlHelper
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_helper = $helper;
        $this->urlDecoder = $urlDecoder;
        $this->urlHelper = $urlHelper;
    }

    public function execute()
    {
        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);

        $q = $this->getRequest()->getParam('q');

        /* @var $query \Magento\Search\Model\Query */
        $query = $this->_queryFactory->get();

        $query->setStoreId($this->_storeManager->getStore()->getId());

        $this->_coreRegistry->register(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY, $query);

        $layout = $this->layoutFactory->create();

        $resultJson = $this->resultJsonFactory->create();

        $beforeUrl = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED);
        $html = $this->_helper->getBlocksHtml($layout);
        if ($beforeUrl) {
            /**
             * by xss protection
             */
            $beforeUrl = $this->urlDecoder->decode($beforeUrl);
            $beforeUrl = $this->urlHelper->getEncodedUrl($beforeUrl);
            $html = str_replace($this->urlHelper->getEncodedUrl(), $beforeUrl, $html);
        }

        return $resultJson->setData([
           'html' => $html
        ]);
    }


}
