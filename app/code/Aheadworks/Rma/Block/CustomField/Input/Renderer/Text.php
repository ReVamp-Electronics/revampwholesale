<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\CustomField\Input\Renderer;

/**
 * Class Text
 * @package Aheadworks\Rma\Block\CustomField\Input\Renderer
 */
class Text extends RendererAbstract
{
    /**
     * @var string
     */
    protected $_template = 'customfield/input/renderer/text.phtml';

    /**
     * @var array
     */
    protected $classNames = ['input-text'];
}
