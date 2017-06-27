<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml\Request;

use \Aheadworks\Rma\Model\Source\Request\Status;

/**
 * Class Edit
 * @package Aheadworks\Rma\Block\Adminhtml\Request
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Aheadworks_Rma';
        $this->_controller = 'adminhtml_request';

        parent::_construct();

        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        /* @var $rmaRequest \Aheadworks\Rma\Model\Request */
        $rmaRequest = $this->_coreRegistry->registry('aw_rma_request');
        if (in_array($rmaRequest->getStatusId(), [Status::CANCELED, Status::CLOSED])) {
            $this->buttonList->remove('save');
            return;
        }
        $requestId = $rmaRequest->getId();
        if ($rmaRequest->getStatusId() == Status::PENDING_APPROVAL) {
            $this->buttonList->add(
                'cancel',
                [
                    'label' => __("Cancel"),
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'save',
                                'target' => '#edit_form',
                                'eventData' => ['action' => ['args' => ['status' => Status::CANCELED]]],
                            ],
                        ],
                    ]
                ]
            );
        }
        if ($rmaRequest->getStatusId() == Status::PACKAGE_RECEIVED) {
            $closeUrl = $this->getUrl(
                "aw_rma_admin/rma/save",
                ['id' => $requestId, 'status' => Status::CLOSED]
            );
            $this->buttonList->add(
                'close',
                [
                    'label' => __("Close"),
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'save',
                                'target' => '#edit_form',
                                'eventData' => ['action' => ['args' => ['status' => Status::CLOSED]]],
                            ],
                        ],
                    ]
                ]
            );
        }
        $this->buttonList->update('save', 'class', 'save');
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ]
        );
        $this->addPrimaryActionButton($rmaRequest);

    }
    public function addPrimaryActionButton($rmaRequest)
    {
        $statusCode = null;
        $buttonLabel = "";
        $back = "";

        $requestStatus = $rmaRequest->getStatusId();
        switch ($requestStatus) {
            case Status::APPROVED:
            case Status::PACKAGE_SENT:
                $statusCode = Status::PACKAGE_RECEIVED;
                $buttonLabel = __('Confirm Package Receiving');
                break;
            case Status::PENDING_APPROVAL:
                if ($rmaRequest->isVirtual()) {
                    $statusCode = Status::CLOSED;
                    $buttonLabel = __('Close');
                } else {
                    $statusCode = Status::APPROVED;
                    $buttonLabel = __('Approve');
                }
                break;
            case Status::PACKAGE_RECEIVED:
                $statusCode = Status::ISSUE_REFUND;
                $buttonLabel = __('Issue Refund');
                $back = 'edit';
                break;
            case Status::ISSUE_REFUND:
                $statusCode = Status::CLOSED;
                $buttonLabel = __('Close');
                $back = 'edit';
                break;
        }
        if ($statusCode) {
            $this->buttonList->add(
                'primaryaction',
                [
                    'label' => $buttonLabel,
                    'class' => 'primary',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'save',
                                'target' => '#edit_form',
                                'eventData' => ['action' => ['args' => ['status' => $statusCode, 'back' => $back]]],
                            ],
                        ],
                    ]
                ]
            );
        }
    }
}
