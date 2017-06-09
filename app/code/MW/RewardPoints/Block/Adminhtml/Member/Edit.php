<?php

namespace MW\RewardPoints\Block\Adminhtml\Member;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
	/**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_escaper = $escaper;
        parent::__construct($context, $data);
    }

	/**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_controller = 'adminhtml_member';

        parent::_construct();

        $this->buttonList->remove('delete');
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
    }

    /**
     * Retrieve text for header element
     *
     * @return string
     */
    public function getHeaderText()
    {
        $customerId = $this->getRequest()->getParam('id');
    	if (isset($customerId)) {
    		$name = $this->_customerFactory->create()->load($customerId)->getName();
    		return $this->_escaper->escapeHtml($name);
    	} else {
    		return '';
    	}
    }
}
