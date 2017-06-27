<?php

namespace MW\RewardPoints\Block\Adminhtml\Cartrules\Edit\Tab;

use MW\RewardPoints\Model\Statusrule;
use Magento\Framework\Stdlib\DateTime;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $_groupFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_storeManager = $storeManager;
        $this->_groupFactory = $groupFactory;
        $this->_backendSession = $backendSession;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'cart_rules_form',
            ['legend' => __('Rule Information')]
        );

        $fieldset->addField(
            'name',
            'text',
            [
                'label'    => __('Rule Name'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'name',
            ]
        );
        $fieldset->addField(
            'description',
            'textarea',
            [
                'name'     => 'description',
                'label'    => __('Description'),
                'class'    => 'required-entry',
                'required' => true,
                'title'    => __('Description'),
            ]
        );
        $fieldset->addField(
            'promotion_message',
            'textarea',
            [
                'name'  => 'promotion_message',
                'label' => __('Promotional Message'),
                'title' => __('Promotional Message'),
                'note'  => __('Choose Yes under Configuration / Display Configuration to display')
            ]
        );
        $fieldset->addField(
            'promotion_image',
            'image',
            [
                'label'    => __('Promotional Banner'),
                'required' => false,
                'name'     => 'promotion_image',
                'note'     => __('Choose Yes under Configuration / Display Configuration to display')
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'label'  => __('Status'),
                'name'   => 'status',
                'values' => Statusrule::getOptionArray(),
                'note'   => __('Enable and Save rule to activate')
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_view',
                'multiselect',
                [
                    'name'     => 'store_view[]',
                    'label'    => __('Store View'),
                    'title'    => __('Store View'),
                    'required' => true,
                    'values'   => $this->_systemStore->getStoreValuesForForm(false, true),
                ]
            );
        } else {
            $fieldset->addField(
                'store_view',
                'hidden',
                [
                    'name'  => 'store_view[]',
                    'value' => $this->_storeManager->getStore(true)->getId()
                ]
            );
        }

        $customerGroups = $this->_groupFactory->create()->getCollection()->load()->toOptionArray();
        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value'] == 0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, ['value' => 0, 'label' => __('NOT LOGGED IN')]);
        }

        $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            [
                'name'     => 'customer_group_ids[]',
                'label'    => __('Customer Groups'),
                'title'    => __('Customer Groups'),
                'required' => true,
                'values'   => $customerGroups,
            ]
        );
        $fieldset->addField(
            'start_date',
            'date',
            [
                'label'  => __('Start Date'),
                'name'   => 'start_date',
                'input_format' => DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => 'yyyy-MM-dd',
            ]
        );
        $fieldset->addField(
            'end_date',
            'date',
            [
                'label'  => __('End Date'),
                'name'   => 'end_date',
                'input_format' => DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => 'yyyy-MM-dd',
                'note'   => __('Leave blank for no time restriction'),
            ]
        );
        $fieldset->addField(
            'rule_position',
            'text',
            [
                'label' => __('Priority'),
                'name'  => 'rule_position',
                'class' => 'validate-digits',
                'note'  => __('"Set Further Rules Processing" under "Actions"'),
            ]
        );

        $dataCartRules = $this->_backendSession->getDataCartRules();
        if ($dataCartRules) {
            $form->setValues($dataCartRules);
            $this->_backendSession->setDataCartRules(null);
        } else {
            $dataCartRules = $this->_coreRegistry->registry('data_cart_rules');
            if ($dataCartRules) {
                $form->setValues($dataCartRules);
            }
        }

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
        return __('Rule information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Rule information');
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
