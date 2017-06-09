<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Xsearch\Block;

use Amasty\Xsearch\Controller\RegistryConstants;
use Magento\Framework\App\Filesystem\DirectoryList;

class Category extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_TEMPLATE_NAME_LENGTH = 'amasty_xsearch/category/name_length';
    const XML_PATH_TEMPLATE_DESC_LENGTH = 'amasty_xsearch/category/desc_length';
    const XML_PATH_TEMPLATE_CATEGORY_LIMIT = 'amasty_xsearch/category/limit';
    const XML_PATH_TEMPLATE_TITLE = 'amasty_xsearch/category/title';

    protected $_template = 'category.phtml';
    protected $_collectionFactory;
    protected $_categoryCollection;
    protected $_coreRegistry;
    protected $_string;
    protected $_xsearchHelper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Amasty\Xsearch\Model\ResourceModel\Category\Fulltext\CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Amasty\Xsearch\Helper\Data $xsearchHelper,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $context->getRegistry();
        $this->_string = $string;
        $this->_xsearchHelper = $xsearchHelper;

        parent::__construct(
            $context,
            $data
        );
    }

    protected function _getCategoryCollection()
    {
        if ($this->_categoryCollection === null) {
            $this->_categoryCollection = $this->_collectionFactory->create();
        }

        return $this->_categoryCollection;
    }

    protected function _getQuery()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY);
    }

    protected function _beforeToHtml()
    {
        $this->_getCategoryCollection()
            ->addNameToResult()
            ->addAttributeToSelect('description')
            ->addUrlRewriteToResult()
            ->addIsActiveFilter()
            ->addSearchFilter($this->_getQuery()->getQueryText())
            ->setPageSize($this->getLimit())
            ->setCurPage(1);

        $this->_getCategoryCollection()->load();

        return parent::_beforeToHtml();
    }

    public function getLoadedCategoryCollection()
    {
        return $this->_getCategoryCollection();
    }

    public function highlight($text)
    {
        return $this->_xsearchHelper->highlight($text, $this->_getQuery()->getQueryText());
    }

    public function getTitle()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_TEMPLATE_TITLE);
    }

    public function getName($_category)
    {
        $nameLength = $this->getNameLength();

        $_nameStripped = $this->stripTags($_category->getName(), null, true);

        $text =
            $this->_string->strlen($_nameStripped) > $nameLength ?
            $this->_string->substr($_nameStripped, 0, $this->getNameLength()) . '...'
            : $_nameStripped;

        return $this->highlight($text);
    }

    public function showDescription($_category)
    {
        return $this->_string->strlen($_category->getDescription()) > 0;
    }

    public function getDescription($_category)
    {
        $descLength = $this->getDescLength();

        $_descStripped = $this->stripTags($_category->getDescription(), null, true);

        $text =
            $this->_string->strlen($_descStripped) > $descLength ?
            $this->_string->substr($_descStripped, 0, $this->getDescLength()) . '...'
            : $_descStripped;

        return $this->highlight($text);
    }

    public function getLimit()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_TEMPLATE_CATEGORY_LIMIT);
    }

    public function getNameLength()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_TEMPLATE_NAME_LENGTH);
    }

    public function getDescLength()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_TEMPLATE_DESC_LENGTH);
    }
}