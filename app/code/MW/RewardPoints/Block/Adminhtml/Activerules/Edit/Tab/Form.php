<?php

namespace MW\RewardPoints\Block\Adminhtml\Activerules\Edit\Tab;

use MW\RewardPoints\Model\Statusrule;
use MW\RewardPoints\Model\Type;
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
     * @var \MW\RewardPoints\Model\ActiverulesFactory
     */
    protected $_activerulesFactory;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
     * @param \MW\RewardPoints\Helper\Data $dataHelper
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
        \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
        \MW\RewardPoints\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_storeManager = $storeManager;
        $this->_groupFactory = $groupFactory;
        $this->_backendSession = $backendSession;
        $this->_activerulesFactory = $activerulesFactory;
        $this->_dataHelper = $dataHelper;
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
        $ruleId = $this->getRequest()->getParam('id');

        $defaultExpired = $this->_activerulesFactory->create()->load($ruleId)->getDefaultExpired();
        if ($defaultExpired == 1) {
            $setDefaultExpired = true;
        } else {
            $setDefaultExpired = false;
        }

        $fieldset = $form->addFieldset(
            'rewardpoints_form',
            ['legend' => __('Change Reward Points Of Customer')]
        );

        $fieldset->addField(
            'rule_name',
            'text',
            [
                'label'    => __('Rule name'),
                'required' => true,
                'name'     => 'rule_name',
            ]
        );
        $fieldset->addField(
            'type_of_transaction',
            'select',
            [
                'label'  => __('Reward for'),
                'class'  => 'required-entry',
                'name'   => 'type_of_transaction',
                'values' => Type::getTypeReward(),
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'label'    => __('Status'),
                'name'     => 'status',
                'required' => true,
                'values'   => Statusrule::getOptionArray(),
                'note'     => __('Enable and Save rule to activate'),
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
                    'name' => 'store_view[]',
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
            'date_event',
            'date',
            [
                'label'  => __('Date Event'),
                'name'   => 'date_event',
                'input_format' => DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => 'yyyy-MM-dd',
            ]
        );
        $fieldset->addField(
            'comment',
            'textarea',
            [
                'label' => __('Comment'),
                'name'  => 'comment',
                'style' => 'height:100px'
            ]
        );
        $fieldset->addField(
            'default_expired',
            'checkbox',
            [
                'label'   => __('Use default point expiration time'),
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                'name'    => 'default_expired',
                'checked' => $setDefaultExpired,
                'note'    => __('Set in Configuration / General Settings')
            ]
        );
        $fieldset->addField(
            'expired_day',
            'text',
            [
                'label' => __('Reward Points Expire in (days)'),
                'class' => 'validate-digits',
                'name'  => 'expired_day',
                'note'  => __('Insert 0 if no limitation.'),
            ]
        );
        $fieldset->addField(
            'reward_point',
            'text',
            [
                'label'    => __('Reward Points'),
                'required' => true,
                'class'    => 'validate-digits',
                'name'     => 'reward_point',
                'note'     => __('Format x (fixed number of points) or x/y (earn x points for every y monetary units spent)'),
            ]
        );

        if ($id = $this->getRequest()->getParam('id')) {
            $type = $this->_activerulesFactory->create()->load($id)->getTypeOfTransaction();
            if ($type == Type::CUSTOM_RULE) {
                $fieldset->addField(
                    'custom_rule',
                    'note',
                    [
                        'label' => __('Referral Link'),
                        'text'  => $this->_dataHelper->getLinkCustomRule($id),
                    ]
                );
            }
        }

        $fieldset->addField(
            'coupon_code',
            'text',
            [
                'label' => __('Coupon code'),
                'name'  => 'coupon_code',
                'class' => 'mw-rewardpoint-validate-coupon-code'
            ]
        );

        $activerulesData = $this->_backendSession->getDataActiverules();
        if ($activerulesData) {
            $form->setValues($activerulesData);
            $this->_backendSession->setDataActiverules(null);
        } else {
            $activerulesData = $this->_coreRegistry->registry('data_activerules');
            $form->setValues($activerulesData);
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
