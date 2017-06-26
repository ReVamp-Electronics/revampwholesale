<?php
namespace Evdpl\Jobopening\Block\Adminhtml\Jobopening\Edit\Tab;

use Evdpl\Jobopening\Helper\Data as HelperData;


class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    protected $helperData;
     protected $_wysiwygConfig;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        HelperData $helperData,
        
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->helperData    = $helperData;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('jobopening');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Evdpl_Jobopening::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('jobopening_main_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Jobopening Information')]);

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Job Title'),
                'title' => __('Job Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

       $fieldset->addField(
        'department',
        'select',
        [
            'name' => 'department',
            'label' => __('Department'),
            'title' => __('Department'),
            'required' => true,
            'values' => $this->helperData->getOptionArray()
        ]
        );
       $fieldset->addField(
       'store_ids',
       'multiselect',
       [
         'name'     => 'store_ids[]',
         'label'    => __('Store Views'),
         'title'    => __('Store Views'),
         'required' => true,
         'values'   => $this->_systemStore->getStoreValuesForForm(false, true),
       ]
    ); 

       $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label'    => __('Job Description'),
                'title'    => __('Job Description'),
                'style' => 'height:36em;',
                'required' => true,
                'config' => $this->_wysiwygConfig->getConfig()
                
            ]
        );
        
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
        'status',
        'select',
        [
            'name' => 'status',
            'label' => __('Open/Closed'),
            'title' => __('Open/Closed'),
            'required' => true,
            'options' => ['1' => __('Open'), '2' => __('Closed')]
        ]
);
        
        $this->_eventManager->dispatch('adminhtml_jobopening_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
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
        return __('Job Opening Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Job Opening Information');
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

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
