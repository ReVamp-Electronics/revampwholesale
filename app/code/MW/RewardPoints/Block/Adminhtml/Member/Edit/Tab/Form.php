<?php

namespace MW\RewardPoints\Block\Adminhtml\Member\Edit\Tab;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
	implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	/**
	 * @var \Magento\Customer\Model\CustomerFactory
	 */
	protected $_customerFactory;

	/**
	 * @var \MW\RewardPoints\Model\CustomerFactory
	 */
	protected $_rwpCustomerFactory;

	/**
	 * @var \MW\RewardPoints\Model\Action
	 */
	protected $_action;

	/**
	 * @var \MW\RewardPoints\Helper\Data
	 */
	protected $_dataHelper;

	/**
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Framework\Data\FormFactory $formFactory
	 * @param \Magento\Customer\Model\CustomerFactory $customerFactory
	 * @param \MW\RewardPoints\Model\CustomerFactory $rwpCustomerFactory
	 * @param \MW\RewardPoints\Model\Action $action
	 * @param \MW\RewardPoints\Helper\Data $dataHelper
	 * @param array $data
	 */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \MW\RewardPoints\Model\CustomerFactory $rwpCustomerFactory,
        \MW\RewardPoints\Model\Action $action,
        \MW\RewardPoints\Helper\Data $dataHelper,
        array $data = []
	) {
		parent::__construct($context, $registry, $formFactory, $data);
		$this->_customerFactory = $customerFactory;
		$this->_rwpCustomerFactory = $rwpCustomerFactory;
		$this->_action = $action;
		$this->_dataHelper = $dataHelper;
	}

	/**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
		/** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('mw_rewardpoints_customer_form_');

		$infoFieldset = $form->addFieldset(
			'base_fieldset',
			['legend'=>__('Reward Points Information')]
		);
		$balanceFieldset = $form->addFieldset(
			'rewardpoints_form',
			['legend'=>__('Manually Adjust Reward Point Balance')]
		);

		$customerId = $this->getRequest()->getParam('id');
		$customer = $this->_customerFactory->create()->load($customerId);
		$customerEmail = $customer->getEmail();
		$points = $this->_rwpCustomerFactory->create()->load($customerId)->getMwRewardPoint();

		$infoFieldset->addField(
			'rewardpoints',
			'note',
			[
				'label'	=> __('Reward Points'),
				'name'	=> 'mw_reward_points',
				'text'	=> $points
			]
		);
		$infoFieldset->addField(
			'customer_email',
			'note',
			[
				'label'	=> __('Customer Email'),
				'text'	=> $this->_dataHelper->getLinkCustomer($customerId, $customerEmail)
			]
		);

		$balanceFieldset->addField(
			'amount',
			'text',
			[
				'label'	=> __('Amount'),
				'name'	=> 'reward_points_amount',
				'class'	=> 'validate-digits'
			]
		);
		$balanceFieldset->addField(
			'action',
			'select',
			[
				'label'		=> __('Action'),
				'name'  	=> 'reward_points_action',
				'options'	=> $this->_action->getOptionArray()
			]
		);

		$balanceFieldset->addField(
			'comment',
			'textarea',
			[
				'label'	=> __('Comment'),
				'name'	=> 'reward_points_comment',
				'style'	=> 'height:100px'
			]
		);

		$form->getElement('action')->setValue(1);
		$this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('General information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
