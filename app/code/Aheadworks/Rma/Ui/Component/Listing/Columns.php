<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Ui\Component\Listing;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory
     */
    protected $componentFactory;

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection
     */
    protected $customFieldCollection;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $componentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection $customFieldCollection,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $componentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->customFieldCollection = $customFieldCollection->setFilterForRmaGrid();
        $this->componentFactory = $componentFactory;
        parent::__construct($context, $components, $data);
    }

    public function prepare()
    {
        foreach ($this->customFieldCollection as $customField) {
            /** @var \Aheadworks\Rma\Model\CustomField $customField */
            $fieldConfig = [
                'filter'        => $customField->getType(),
                'label'         => __($customField->getName()),
                'dataType'      => $customField->getType(),
                'visible'       => false
            ];
            if ($customField->getName() == 'Resolution') {
                $fieldConfig['visible'] = true;
                $fieldConfig['sortOrder'] = 6;
            }
            if ($customField->getType() == \Aheadworks\Rma\Model\Source\CustomField\Type::SELECT_VALUE) {
                $fieldConfig['component'] = 'Magento_Ui/js/grid/columns/select';
                $fieldConfig['options'] = $customField->toOptionArray();
            }
            $arguments = [
                'data' => [
                    'config' => $fieldConfig
                ],
                'context' => $this->getContext()
            ];
            $columnName = "cf{$customField->getId()}_value";
            $column = $this->componentFactory->create($columnName, 'column', $arguments);
            $column->prepare();
            $this->addComponent($columnName, $column);
        }
        parent::prepare();
    }
}
