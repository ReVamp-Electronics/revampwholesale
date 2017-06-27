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
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Search\Model\QueryFactory;
class Recent extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_TEMPLATE_LIMIT = 'amasty_xsearch/recent_searches/limit';
    const XML_PATH_TEMPLATE_TITLE = 'amasty_xsearch/recent_searches/title';

    protected $_template = 'search/recent.phtml';
    protected $_queryCollectionFactory;
    protected $_queryFactory;

    protected $_searchCollection;
    protected $_coreRegistry;
    protected $_string;
    protected $_xsearchHelper;
    protected $_searchHelper;
    protected $_resourceHelper;
    protected $_query;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        QueryFactory $queryFactory,
        QueryCollectionFactory $queryCollectionFactory,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Amasty\Xsearch\Helper\Data $xsearchHelper,
        \Magento\Search\Helper\Data $searchHelper,
        \Magento\Framework\DB\Helper $resourceHelper,
        array $data = []
    ) {
        $this->_queryFactory = $queryFactory;
        $this->_queryCollectionFactory = $queryCollectionFactory;
        $this->_coreRegistry = $context->getRegistry();
        $this->_string = $string;
        $this->_xsearchHelper = $xsearchHelper;
        $this->_searchHelper = $searchHelper;
        $this->_resourceHelper = $resourceHelper;

        parent::__construct(
            $context,
            $data
        );
    }

    protected function _getSearchCollection()
    {
        if ($this->_searchCollection === null) {
            $this->_searchCollection = $this->_queryCollectionFactory->create();
        }

        return $this->_searchCollection;
    }

    protected function _getQuery()
    {
        if (!$this->_query){
            $this->_query = $this->_coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY);
        }

        if (!$this->_query) {
            $this->_query = $this->_queryFactory->get();
        }

        return $this->_query;
    }

    protected function _beforeToHtml()
    {
        $this->_getSearchCollection()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setRecentQueryFilter()
            ->setPageSize($this->getLimit())
            ->setCurPage(1)
            ->getSelect()
                ->where(
                    'num_results > 0 AND display_in_terms = 1'
                );



        if ($this->_getQuery()){
            $queryText = $this->_getQuery()->getQueryText();
            if (!empty($queryText)) {
                $this->_getSearchCollection()
                    ->getSelect()
                    ->where('query_text LIKE ?',
                                    $this->_resourceHelper->addLikeEscape($this->_getQuery()->getQueryText(), ['position' => 'start']));
            }
        }

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
