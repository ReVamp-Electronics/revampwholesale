<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Rma\Block\Adminhtml\Request\Edit\Tabs;

use \Aheadworks\Rma\Model\Source\CustomField\Type;
use \Aheadworks\Rma\Model\Source\CustomField\Refers;

class Request extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection
     */
    protected $customFieldCollection;

    /**
     * @var \Aheadworks\Rma\Model\Source\Request\Status
     */
    protected $statusSource;

    /**
     * @param \Aheadworks\Rma\Model\ResourceModel\CustomField\CollectionFactory $customFieldCollectionFactory
     * @param \Aheadworks\Rma\Model\Source\Request\Status $statusSource
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Aheadworks\Rma\Model\ResourceModel\CustomField\CollectionFactory $customFieldCollectionFactory,
        \Aheadworks\Rma\Model\Source\Request\Status $statusSource,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->customFieldCollection = $customFieldCollectionFactory->create()
            ->addRefersToFilter(Refers::REQUEST_VALUE);
        $this->statusSource = $statusSource;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Aheadworks\Rma\Model\Request $rmaRequest */
        $rmaRequest = $this->_coreRegistry->registry('aw_rma_request');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('request_');
        $fieldset = $form->addFieldset('request_fieldset', []);

        $fieldset->addField('id', 'hidden', ['name' => 'request_id', 'value' => $rmaRequest->getId()]);
        $fieldset->addField(
            'author_link_title',
            'link',
            [
                'label' => __('Order'),
                'title' => __('Order'),
                'value' => "#" . $rmaRequest->getOrder()->getIncrementId(),
                'href'  => $this->getUrl(
                    'sales/order/view',
                    ['order_id' => $rmaRequest->getOrderId()]
                ),
                'target' => '_blank',
                'note' => $rmaRequest->getOrder()->getCreatedAtFormatted(\IntlDateFormatter::MEDIUM)
            ]

        );
        $fieldset->addField(
            'status_text',
            'label',
            [
                'name'  => 'status_text',
                'label' => __("Status"),
                'title' => __("Status"),
                'value' => $this->statusSource->getOptionLabelByValue($rmaRequest->getStatusId())
            ]
        );

        foreach ($this->customFieldCollection as $customField) {
            $isDisabled = !in_array($rmaRequest->getStatusId(), $customField->getEditableAdminForStatusIds());
            $fieldConfig = [
                'name'      => "custom_fields[{$customField->getId()}]",
                'label'     => $customField->getName(),
                'title'     => $customField->getName(),
                'required'  => $customField->getIsRequired() && !$isDisabled,
                'disabled'  => $isDisabled,
                'value'     => $rmaRequest->getCustomFields($customField->getId())
            ];
            if (in_array($customField->getType(), [Type::SELECT_VALUE, Type::MULTI_SELECT_VALUE])) {
                $fieldConfig['values'] = $customField->toOptionArray();
            }
            if ($customField->getType() == Type::MULTI_SELECT_VALUE && !$isDisabled) {
                //hack for saving empty multiselects
                $fieldset->addField(
                    "hidden{$customField->getId()}",
                    "hidden",
                    ['name' => "custom_fields[{$customField->getId()}]"]);
            }
            $fieldset->addField(
                $customField->getId(),
                $customField->getType(),
                $fieldConfig
            );
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
