<?php

namespace MW\RewardPoints\Block\Adminhtml\Member\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
	/**
	 * @var \MW\RewardPoints\Helper\Data
	 */
	protected $_dataHelper;

	/**
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
	 * @param \Magento\Backend\Model\Auth\Session $authSession
	 * @param \MW\RewardPoints\Helper\Data $dataHelper
	 * @param array $data
	 */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \MW\RewardPoints\Helper\Data $dataHelper,
        array $data = []
	) {
		parent::__construct($context, $jsonEncoder, $authSession, $data);
		$this->_dataHelper = $dataHelper;
	}

	/**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('member_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Reward Points Member Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
    	$this->addTab(
    		'form_member_detail',
    		[
    			'label'     => __('General information'),
				'title'     => __('General information'),
				'content'   => $this->getLayout()->createBlock(
					'MW\RewardPoints\Block\Adminhtml\Member\Edit\Tab\Form'
				)->toHtml()
    		]
		);

		$this->addTab(
			'form_member_transaction',
			[
				'label'     => __('Transaction History'),
				'title'     => __('Transaction History'),
				'content'   => $this->getLayout()->createBlock(
					'MW\RewardPoints\Block\Adminhtml\Member\Edit\Tab\Transaction'
				)->toHtml()
			]
		);

    	return parent::_beforeToHtml();
    }
}
