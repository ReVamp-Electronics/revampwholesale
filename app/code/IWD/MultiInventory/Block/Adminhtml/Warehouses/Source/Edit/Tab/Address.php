<?php

namespace IWD\MultiInventory\Block\Adminhtml\Warehouses\Source\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;

/**
 * Class Address
 * @package IWD\MultiInventory\Block\Adminhtml\Warehouses\Source\Edit\Tab
 */
class Address extends Generic implements TabInterface
{
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    private $countryFactory;

    /**
     * @var RegionCollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Country $countryFactory
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Country $countryFactory,
        RegionCollectionFactory $regionCollectionFactory,
        array $data = []
    ) {
        $this->countryFactory = $countryFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('iwd_om_source');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('address_');
        $form->setFieldNameSuffix('address');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Source Address')]
        );

        $fieldset->addField(
            'street',
            'text',
            [
                'name'     => 'street',
                'label'    => __('Street'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'city',
            'text',
            [
                'name'     => 'city',
                'label'    => __('City'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'country_id',
            'select',
            [
                'name'      => 'country_id',
                'label'     => __('Country'),
                'title'     => __('Country'),
                'values'    => $this->countryFactory->toOptionArray(),
                'required'  => false
            ]
        )->setAfterElementHtml(
            $this->getJsForSelectRegion()
        );

        $fieldset->addField(
            'region_id',
            'select',
            [
                'name'      => 'region_id',
                'label'     => __('Region'),
                'title'     => __('Region'),
                'values'    =>  ['--Please Select Country--'],
                'required'  => false
            ]
        );

        $fieldset->addField(
            'region',
            'text',
            [
                'name'      => 'region',
                'label'     => __('Region'),
                'title'     => __('Region'),
                'required'  => false
            ]
        );

        $fieldset->addField(
            'postcode',
            'text',
            [
                'name'     => 'postcode',
                'label'    => __('Postcode'),
                'required' => false
            ]
        );

        $data = $model->getData();
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    private function getJsForSelectRegion()
    {
        $model = $this->_coreRegistry->registry('iwd_om_source');
        $countryRegionsJson = json_encode($this->getCountryRegions());
        $countryId = $model->getData('country_id');
        $regionId = $model->getData('region_id');

        return '
        <script type="text/javascript">
            require(["jquery", "jquery/ui"],
            function($j, mageTemplate) {
                var countryRegions= ' . $countryRegionsJson . ';
                $j("#edit_form").on("change", "#address_country_id",
                    function(event){
                        var id = $j("#address_country_id").val();
                        initRegion(id, null);
                });
                function initRegion(countryId, regionId)
                {
                     if (countryRegions[countryId]) {
                         var regionOptions = $j("#address_region_id");
                         regionOptions.find("option").remove();
                         $j.each(countryRegions[countryId], function(i, val){
                             var opt = $j("<option />");
                             opt.val(i).text(val);
                             if (i == regionId) {
                                 opt.attr("selected", "selected");
                             }
                             regionOptions.append(opt);
                         });
                         $j(".field-region").hide();
                         $j(".field-region_id").show();
                     } else {
                         $j(".field-region").show();
                         $j(".field-region_id").hide();
                     }
                }
                initRegion("' . $countryId . '", "' . $regionId . '");
            });
        </script>';
    }

    /**
     * @return array
     */
    private function getCountryRegions()
    {
        $countryRegions = [];

        $regionsCollection = $this->regionCollectionFactory->create();
        foreach ($regionsCollection as $region) {
            $countryRegions[$region->getCountryId()][$region->getId()] = $region->getDefaultName();
        }

        return $countryRegions;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Address');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Address');
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
