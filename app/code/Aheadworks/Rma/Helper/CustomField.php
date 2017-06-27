<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Helper;

use Aheadworks\Rma\Model\Source\CustomField\Type;

/**
 * Class CustomField
 * @package Aheadworks\Rma\Helper
 */
class CustomField extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    private $rendererClassMap = [
        Type::TEXT_VALUE            => 'Aheadworks\Rma\Block\CustomField\Input\Renderer\Text',
        Type::TEXT_AREA_VALUE       => 'Aheadworks\Rma\Block\CustomField\Input\Renderer\Textarea',
        Type::SELECT_VALUE          => 'Aheadworks\Rma\Block\CustomField\Input\Renderer\Select',
        Type::MULTI_SELECT_VALUE    => 'Aheadworks\Rma\Block\CustomField\Input\Renderer\Multiselect'
    ];

    /**
     * @param int $type
     * @return string
     */
    public function getElementRendererClass($type)
    {
        return $this->rendererClassMap[$type];
    }
}