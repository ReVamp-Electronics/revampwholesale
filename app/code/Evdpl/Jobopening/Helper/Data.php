<?php

namespace Evdpl\Jobopening\Helper;
 
use Magento\Customer\Model\Session as CustomerSession;
 

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    )
    {
        parent::__construct($context);
    }
 
    public function getOptionArray()
    {
        return [
           
            [       'label' => '',
                    'value' => [
                    ['value' => '1', 'label' => __('Customer Relations')],
                    ['value' => '2', 'label' => __('Warehouse & Distribution')],
                    ['value' => '3', 'label' => __('Sales & Marketing')],
                    ['value' => '4', 'label' => __('Return Merchandise Authorization')],
                    ['value' => '5', 'label' => __('Creative Media')],
                ],
            ],
        ];
    }
 
}

 ?>