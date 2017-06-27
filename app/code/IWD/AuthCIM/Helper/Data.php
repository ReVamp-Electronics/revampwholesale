<?php

namespace IWD\AuthCIM\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 * @package IWD\AuthCIM\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var array
     */
    public static $baseCardTypes = [
        'AE' => ['American Express', 'AmericanExpress'],
        'VI' => 'Visa',
        'MC' => 'MasterCard',
        'DI' => 'Discover',
        'MI' => 'Maestro',
        'JBC' => 'JBC',
        'CUP' => ['China Union Pay', 'ChinaUnionPay'],
    ];

    /**
     * @param $code
     * @return mixed
     */
    public static function getCreditCardType($code)
    {
        $types = self::$baseCardTypes;
        return isset($types[$code])
            ? (is_array($types[$code]) ? $types[$code][0] : $types[$code])
            : $code;
    }

    /**
     * @param $type
     * @return mixed
     */
    public static function getCreditCardTypeCode($type)
    {
        foreach (self::$baseCardTypes as $key => $label) {
            if ($type == $key
                || (is_string($label) && $type == $label)
                || (is_array($label) && in_array($type, $label))
            ) {
                return $key;
            }
        }
        return $type;
    }
}
