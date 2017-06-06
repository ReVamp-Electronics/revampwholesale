<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Rma\Block\Adminhtml\Request\Edit\Tabs;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use \Aheadworks\Rma\Model\Source\CustomField\Type;
use \Aheadworks\Rma\Model\Source\CustomField\Refers;

class Products extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Aheadworks\Rma\Model\CustomFieldFactory
     */
    protected $customFieldFactory;

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection
     */
    protected $customFieldCollection;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory
     * @param \Aheadworks\Rma\Model\ResourceModel\CustomField\CollectionFactory $customFieldCollectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory,
        \Aheadworks\Rma\Model\ResourceModel\CustomField\CollectionFactory $customFieldCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->customFieldFactory = $customFieldFactory;
        $this->customFieldCollection = $customFieldCollectionFactory->create()
            ->addRefersToFilter(Refers::ITEM_VALUE)
            ->addFieldToFilter('is_system', false)
        ;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    
    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Aheadworks\Rma\Model\Request $rmaRequest */
        $rmaRequest = $this->_coreRegistry->registry('aw_rma_request');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('products_');
        
        foreach ($rmaRequest->getItemsCollection() as $item) {
            $fieldset = $form->addFieldset("item_{$item->getId()}_fieldset", []);
            if ($item->getProductId()) {
                $fieldset->addField(
                    "name{$item->getId()}",
                    'link',
                    [
                        'value' => $item->getName(),
                        'href'  => $this->getUrl(
                            'catalog/product/edit',
                            ['id' => $item->getProductId()]
                        ),
                        'target' => '_blank',
                        'css_class' =>'product-name',
                        'note' => "SKU: " . $item->getSku()
                    ]
                );
            } else {
                $fieldset->addField(
                    "name{$item->getId()}",
                    'label',
                    [
                        'value' => $item->getName(),
                        'css_class' =>'product-name',
                        'note' => "SKU: " . $item->getSku()
                    ]
                );
            }
            $fieldset->addField(
                "reason{$item->getId()}",
                'label',
                [
                    'label' => __('Reason'),
                    'value' => $this->getItemReason($item)
                ]
            );
            $fieldset->addField(
                "qty{$item->getId()}",
                'label',
                [
                    'label' => __('Qty'),
                    'value' => $item->getQty(),
                ]
            );
            $fieldset->addField(
                "price{$item->getId()}",
                'note',
                [
                    'label' => __('Price'),
                    'text' => $this->priceCurrency->format($item->getPrice()),
                ]
            );
            foreach ($this->customFieldCollection as $customField) {
                $isDisabled = !in_array($rmaRequest->getStatusId(), $customField->getEditableAdminForStatusIds());
                $fieldConfig = [
                    'name'      => "items[{$item->getId()}][custom_fields][{$customField->getId()}]",
                    'label'     => $customField->getName(),
                    'title'     => $customField->getName(),
                    'required'  => $customField->getIsRequired() && !$isDisabled,
                    'disabled'  => $isDisabled,
                    'value'     => $item->getCustomFields($customField->getId())
                ];
                if (in_array($customField->getType(), [Type::SELECT_VALUE, Type::MULTI_SELECT_VALUE])) {
                    $fieldConfig['values'] = $customField->toOptionArray();
                }
                if ($customField->getType() == Type::MULTI_SELECT_VALUE && !$isDisabled) {
                    //hack for saving empty multiselects
                    $fieldset->addField(
                        "item{$item->getId()}_hidden_cf{$customField->getId()}",
                        "hidden",
                        ['name' => "items[{$item->getId()}][custom_fields][{$customField->getId()}]"]);
                }
                $fieldset->addField(
                    "item{$item->getId()}_cf{$customField->getId()}",
                    $customField->getType(),
                    $fieldConfig
                );
            }
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getItemReason(\Aheadworks\Rma\Model\RequestItem $item)
    {
        $reasonCustomField = $this->customFieldFactory->create()->load('Reason', 'name');
        $optionValue = $item->getCustomFields($reasonCustomField->getId());
        return $reasonCustomField->getOptionLabelByValue($optionValue);
    }

}
