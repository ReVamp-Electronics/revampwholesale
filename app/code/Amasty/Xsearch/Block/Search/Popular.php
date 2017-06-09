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
namespace Amasty\Xsearch\Block\Search;

use Amasty\Xsearch\Controller\RegistryConstants;
use Magento\Search\Model\QueryFactory;

class Popular extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_TEMPLATE_LIMIT = 'amasty_xsearch/popular_searches/limit';
    const XML_PATH_TEMPLATE_TITLE = 'amasty_xsearch/popular_searches/title';

    protected $_template = 'search/popular.phtml';
    protected $_queryFactory;
    protected $_searchCollection;
    protected $_coreRegistry;
    protected $_string;
    protected $_xsearchHelper;
    protected $_searchHelper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        QueryFactory $queryFactory,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Amasty\Xsearch\Helper\Data $xsearchHelper,
        \Magento\Search\Helper\Data $searchHelper,
        array $data = []
    ) {
        $this->_queryFactory = $queryFactory;
        $this->_coreRegistry = $context->getRegistry();
        $this->_string = $string;
        $this->_xsearchHelper = $xsearchHelper;
        $this->_searchHelper = $searchHelper;

        parent::__construct(
            $context,
            $data
        );
    }

    protected function _getSearchCollection()
    {
        if ($this->_searchCollection === null) {
            $this->_searchCollection = $this->_queryFactory->get()->getSuggestCollection();
        }

        return $this->_searchCollection;
    }

    protected function _getQuery()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY);
    }

    protected function _beforeToHtml()
    {
        $this->_getSearchCollection()
            ->setPageSize($this->getLimit())
            ->setCurPage(1);

        $this->_getSearchCollection()->load();

        return parent::_beforeToHtml();
    }

    public function getLoadedSearchCollection()
    {
        return $this->_getSearchCollection();
    }

    public function getTitle()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_TEMPLATE_TITLE);
    }

    public function getName($_search)
    {
        $text = $this->stripTags($_search->getQueryText(), null, true);

        return $this->highlight($text);
    }

    public function getSearchUrl($_search)
    {
        return $this->_searchHelper->getResultUrl($_search->getQueryText());
    }

    public function highlight($text)
    {
        if ($this->_getQuery()){
            $text = $this->_xsearchHelper->highlight($text, $this->_getQuery()->getQueryText());
        }
        return $text;
    }

    public function getLimit()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_TEMPLATE_LIMIT);
    }
}
