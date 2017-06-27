<?php

namespace IWD\SalesRep\Block\Adminhtml\User\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use IWD\SalesRep\Helper\Data as SalesrepHelper;

/**
 * Class SalesRep
 * @package IWD\SalesRep\Block\Adminhtml\User\Edit\Tab
 */
class SalesRep extends Generic
{
    /**
     * @var Yesno
     */
    private $yesNo;

    /**
     * @var \IWD\SalesRep\Model\UserFactory
     */
    private $salesrepUserFactory;

    /**
     * SalesRep constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \IWD\SalesRep\Model\UserFactory $salesrepUserFactory
     * @param Yesno $yesNo
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \IWD\SalesRep\Model\UserFactory $salesrepUserFactory,
        Yesno $yesNo,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->salesrepUserFactory = $salesrepUserFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->salesrepUserFactory->create();
        $adminUser = $this->_coreRegistry->registry('permissions_user');
        if ($adminUser->getId()) {
            $model = $model->load($adminUser->getId(), \IWD\SalesRep\Model\User::ADMIN_ID);
        }

        if ($model->isObjectNew() && $this->_request->getParam(SalesrepHelper::HTTP_REFERRER_KEY) == SalesrepHelper::HTTP_REFERRER) {
            $model->setEnabled(true);
        }

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'iwd_salesrep_user',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $form->setHtmlIdPrefix('_account');
        $form->setFieldNameSuffix('salesrep');

        $fieldset = $form->addFieldset(
            'salesrep',
            ['legend' => __('Sales Representative')]
        );

        $fieldset->addField(
            'salesrep_enabled',
            'select',
            [
                'name' => 'enabled',
                'label' => __('Active'),
                'values' => $this->yesNo->toOptionArray(),
                'value' => $model->getEnabled(),
                'data-form-part' => $this->getData('target_form'),
            ]
        );

        if ($this->_request->getParam(SalesrepHelper::HTTP_REFERRER_KEY) == SalesrepHelper::HTTP_REFERRER) {
            $fieldset->addField(
                'salesrep_referrer',
                'hidden',
                [
                    'name'        => SalesrepHelper::HTTP_REFERRER_KEY,
                    'value' => SalesrepHelper::HTTP_REFERRER,
                    'data-form-part' => $this->getData('target_form'),
                ]
            );
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
