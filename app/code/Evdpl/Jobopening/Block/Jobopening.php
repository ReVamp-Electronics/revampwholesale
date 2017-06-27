<?php
namespace Evdpl\Jobopening\Block;

/**
 * News content block
 */
class Jobopening extends \Magento\Framework\View\Element\Template
{
    
    protected $_jobopeningCollection = null;
    
  
    protected $_jobopeningCollectionFactory;
    
   
    protected $_dataHelper;
    
   
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Evdpl\Jobopening\Model\ResourceModel\Jobopening\CollectionFactory $jobopeningCollectionFactory,
        \Evdpl\Jobopening\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_jobopeningCollectionFactory = $jobopeningCollectionFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct(
            $context,
            $data
        );
    }
    
    /**
     * Retrieve news collection
     *
     * @return Magentostudy_News_Model_ResourceModel_News_Collection
     */
    protected function _getCollection()
    {
        $collection = $this->_jobopeningCollectionFactory->create();
        return $collection;
    }
    
    /**
     * Retrieve prepared news collection
     *
     * @return Magentostudy_News_Model_Resource_News_Collection
     */
    public function getCollection()
    {
        if (is_null($this->_jobopeningCollection)) {
            $this->_jobopeningCollection = $this->_getCollection();
            $this->_jobopeningCollection->setCurPage($this->getCurrentPage());
           // $this->_jobopeningCollection->setPageSize($this->_dataHelper->getNewsPerPage());
            //$this->_jobopeningCollection->setOrder('published_at','asc');
        }

        return $this->_jobopeningCollection;
    }
    
   
    public function getCurrentPage()
    {
        return $this->getData('current_page') ? $this->getData('current_page') : 1;
    }
    
    
    public function getItemUrl($jobopeningItem)
    {
        return $this->getUrl('*/*/view', array('id' => $jobopeningItem->getId()));
    }

  
    
    /**
     * Get a pager
     *
     * @return string|null
     */
    public function getPager()
    {
        
    }
}
