<?php

namespace MW\RewardPoints\Block\Adminhtml\Activerules;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_escaper = $escaper;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_controller = 'adminhtml_activerules';

        parent::_construct();

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ],
            ],
            10
        );
    }

    public function getHeaderText()
    {
        $activeRulesData = $this->_coreRegistry->registry('data_activerules');
        if ($activeRulesData && $activeRulesData->getId()) {
            return __("Edit Rule '%1'", $this->_escaper->escapeHtml($activeRulesData->getName()));
        } else {
            return __('New Rule');
        }
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            require([
                'jquery',
                'prototype',
                'mwValidate'
            ], function(jQuery) {
                jQuery(document).ready(function() {
                    if ($('default_expired').checked == false) {
                        if ($('expired_day')) {
                            $('expired_day').up(1).show();
                        }
                    } else {
                        if ($('expired_day')) {
                            $('expired_day').up(1).hide();
                        }
                    }

                    $('default_expired').observe('click', function() {
                        if ($('default_expired').checked == false) {
                            if ($('expired_day')) {
                                $('expired_day').up(1).show();
                            }
                        } else {
                            if ($('expired_day')) {
                                $('expired_day').up(1).hide();
                            }
                        }
                    });

                    if ($('type_of_transaction').value == 6 || $('type_of_transaction').value == 14) {
                        if ($('reward_point').hasClassName('validate-digits') == true) {
                            $('reward_point').removeClassName('validate-digits');
                        }

                        if ($('note_reward_point')) {
                            $('note_reward_point').show();
                        }
                    } else {
                        if ($('reward_point').hasClassName('validate-digits')== false) {
                            $('reward_point').addClassName('validate-digits');
                        }

                        if ($('note_reward_point')) {
                            $('note_reward_point').hide();
                        }

                        if ($('type_of_transaction').value == 27) {
                            if ($('comment')) {
                                $('comment').up(1).show();
                            }
                            if ($('comment')) {
                                $('date_event').up(1).show();
                            }
                        } else {
                            if ($('comment')) {
                                $('comment').up(1).hide();
                            }
                            if ($('comment')) {
                                $('date_event').up(1).hide();
                            }
                        }
                    }

                    if ($('type_of_transaction').value == 51) {
                        if ($('coupon_code')) {
                            $('coupon_code').up(1).show();
                        }
                    } else {
                        if ($('coupon_code')) {
                            $('coupon_code').up(1).hide();
                        }
                    }

                    $('type_of_transaction').observe('change', function() {
                        if ($('type_of_transaction').value == 51) {
                            if ($('coupon_code')) {
                                $('coupon_code').up(1).show();
                            }
                        } else {
                            if ($('coupon_code')) {
                                $('coupon_code').up(1).hide();
                            }
                        }

                        if ($('type_of_transaction').value == 6 || $('type_of_transaction').value == 14) {
                            if ($('reward_point').hasClassName('validate-digits') == true) {
                                $('reward_point').removeClassName('validate-digits');
                            }
                            if ($('note_reward_point')) {
                                $('note_reward_point').show();
                            }
                        } else {
                            if ($('reward_point').hasClassName('validate-digits') == false) {
                                $('reward_point').addClassName('validate-digits');
                            }
                            if ($('note_reward_point')) {
                                $('note_reward_point').hide();
                            }

                            if ($('type_of_transaction').value == 27) {
                                if ($('comment')) {
                                    $('comment').up(1).show();
                                }
                                if ($('comment')) {
                                    $('date_event').up(1).show();
                                }
                            } else {
                                if ($('comment')) {
                                    $('comment').up(1).hide();
                                }
                                if ($('comment')) {
                                    $('date_event').up(1).hide();
                                }
                            }
                        }
                    });
                });
            });
        ";

        return parent::_prepareLayout();
    }
}
