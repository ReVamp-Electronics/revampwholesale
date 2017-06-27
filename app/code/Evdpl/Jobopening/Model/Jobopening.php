<?php
namespace Evdpl\Jobopening\Model;


class Jobopening extends \Magento\Framework\Model\AbstractModel
{
   
   const STATUS_OPEN = 1;

    const STATUS_CLOSED = 0;

    protected $_customersFactory;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Evdpl\Jobopening\Model\ResourceModel\Jobopening');
    }

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customersFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
       
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
       
    }
    
    
    /**
     * Retrieve store where customer was created
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore($storeId)
    {
        return $this->_storeManager->getStore($storeId);
    }
     public function getOptionArray()
    {
         return ['1' => __('Open'), '2' => __('Closed')];
    }
    public function getOptionArrayForDep()
    {
        return  ['1' => __('Customer Relations'), '2' => __('Warehouse & Distribution'), '3' => __('Sales & Marketing'), '4' => __('Return Merchandise Authorization'), '5' => __('Creative Media')];
        
    }
    

}
