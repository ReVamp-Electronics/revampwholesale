<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Model\Validation;

class Nickname
{
    protected $_value = 'validate-nickname';

    /**
     * Retrieve custom values
     *
     * @return array
     */
    public function getValues()
    {
        $values = array('value' => $this->_value,
                        'label' => __(
                            'Nickname validation'
                        )
        );
        return $values;
    }

    /**
     * Retrieve JS code
     *
     * @return string
     */
    public function getJS()
    {
        $message = __(
            'Please use only letters (a-z or A-Z), numbers (0-9), "_" and "-" symbols.'
        );

        $js
            = '
           require([
            \'jquery\',
            \'jquery/validate\'
             ], function ($) {
              $.validator.addMethod(\''. $this->_value .'\', function (value, element)
                {
                    return this.optional(element) ||  /^[-0-9A-Za-z_\s]+$/.test(value);
                }, \'' . $message . '\');
            });';


        return $js;
    }
}
