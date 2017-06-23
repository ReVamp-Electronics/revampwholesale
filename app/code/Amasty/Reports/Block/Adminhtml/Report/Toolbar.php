<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report;

use Amasty\Reports\Block\Adminhtml\Menu;
use Amasty\Reports\Block\Adminhtml\Navigation;
use Amasty\Reports\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Toolbar extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var Store
     */
    protected $systemStore;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Collection
     */
    protected $eavCollection;

    /**
     * @var Navigation
     */
    private $navigation;

    /**
     * Toolbar constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param Data $helper
     * @param Navigation $navigation
     * @param Collection $eavCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        Data $helper,
        Navigation $navigation,
        Collection $eavCollection,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->helper = $helper;
        $this->eavCollection = $eavCollection;
        $this->navigation = $navigation;
    }


    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'report_toolbar',
                'action' => '',
            ]
        ]);

        $this->addControls($form);

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @param AbstractForm $parentElement
     *
     * @return $this
     */
    protected function addDateControls(AbstractForm $parentElement)
    {
        $dateFormat = 'y-MM-dd';

        $parentElement->addField('from', 'date', [
            'label'         => __('From'),
            'name'          => 'from',
            'date_format'   => $dateFormat,
            'format'        => $dateFormat,
            'value'         => $this->_localeDate->date($this->helper->getDefaultFromDate())
        ]);

        $parentElement->addField('to', 'date', [
            'label'         => __('To'),
            'name'          => 'to',
            'format'        => $dateFormat,
            'date_format'   => $dateFormat,
            'value'         => $this->_localeDate->date()
        ]);

        return $this;
    }

    /**
     * @param AbstractForm $form
     *
     * @return $this
     */
    protected function addControls(AbstractForm $form)
    {
        $form->addField('store', 'select', [
            'name'      => 'store',
            'values'    => $this->systemStore->getStoreValuesForForm(false, false),
            'class'     => 'right',
            'no_span'   => true
        ]);

        return $this;
    }

    public function getCurrentTitle()
    {
        $title = $this->navigation->getCurrentTitle();
        return $title;
    }
}
