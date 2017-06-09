<?php

namespace MW\RewardPoints\Block\Adminhtml\Products\Import\Edit\Tab;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	/**
	 * @var \Magento\Store\Model\ResourceModel\Website\Collection
	 */
	protected $_websiteCollection;

	/**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\ResourceModel\Website\Collection $websiteCollection
     * @param array $data
     */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\ResourceModel\Website\Collection $websiteCollection,
        array $data = []
	) {
		parent::__construct($context, $registry, $formFactory, $data);
		$this->_websiteCollection = $websiteCollection;
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
        $form->setHtmlIdPrefix('mw_rewardpoints_import_');

        $fieldset = $form->addFieldset(
            'rewardpoints_form',
            ['legend' => __('Import Product Reward Points')]
        );

        $fieldset->addField(
            'website_id',
            'select',
            [
                'name' 		=> 'website_id',
                'label' 	=> __('Website'),
                'required' 	=> true,
                'values'	=> $this->_websiteCollection->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'filename',
            'file',
            [
                'name'      => 'filename',
                'label'     => __('CSV File'),
                'required'  => true,
                'note'      => __('CSV (Product Id, SKUs, Reward Points)')
            ]
        );

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
        return __('Import Product Reward Points');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Import Product Reward Points');
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
