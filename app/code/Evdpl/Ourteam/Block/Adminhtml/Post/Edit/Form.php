<?php
namespace Evdpl\Ourteam\Block\Adminhtml\Post\Edit;

/**
 * Adminhtml ourteam post edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    protected $_wysiwygConfig;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('post_form');
        $this->setTitle(__('Team Member Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Evdpl\Ourteam\Model\Post $model */
        $model = $this->_coreRegistry->registry('ourteam_post');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post','enctype' => 'multipart/form-data']]
        );

        $form->setHtmlIdPrefix('post_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getPostId()) {
            $fieldset->addField('post_id', 'hidden', ['name' => 'post_id']);
        }

        $fieldset->addField(
            'title',
            'text',
            ['name' => 'title', 'label' => __('Member name'), 'title' => __('Member name'), 'required' => true]
        );

        $fieldset->addField(
            'designation',
            'text',
            [
                'name' => 'designation',
                'label' => __('Member Designation'),
                'title' => __('Member Designation'),
                'required' => true,
                'class' => 'designation'
            ]
        );

    
         $fieldset->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'label' => __('Member Image'),
                'title' => __('Member Image'),
                'required' => true
                
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
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );
        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }
        $wysiwygConfig = $this->_wysiwygConfig->getConfig();
        $fieldset->addField(
            'content',
            'editor',
            [
                'name' => 'content',
                'label' => __('About Member'),
                'title' => __('About Member'),
                'style' => 'height:36em',
                'required' => true,
                'config'    => $wysiwygConfig
            ]
        );
        $fieldset->addField(
            'displayorder',
            'text',
            [
                'name' => 'displayorder',
                'label' => __('Display Order'),
                'title' => __('Display Order'),
                'required' => true,
                'value' =>'abc'
            ]
        );
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
