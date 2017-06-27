<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Evdpl\Faques\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Question extends Template
{
    /**
     * @param Template\Context $context
     * @param array $data
     */

    protected $_questionFactory;

    

    public function __construct(Template\Context $context, array $data = [], \Evdpl\Faques\Model\QuestionFactory $questionFactory)
    {
    	$this->_questionFactory = $questionFactory;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }
     protected function _prepareCollection()
    {

        parent::_prepareCollection();
        return $this;
    }

    public function getFaqs()
    {
    	  $collection = $this->_questionFactory->create()->getCollection();
        $collection->addFieldToFilter('status', 1)->setOrder('displayorder','asc');
    	  $collection->getSelect();	
    	  return $collection;
    }
}
