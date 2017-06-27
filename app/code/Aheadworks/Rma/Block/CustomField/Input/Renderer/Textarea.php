<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\CustomField\Input\Renderer;
/**
 * Class Textarea
 * @package Aheadworks\Rma\Block\CustomField\Input\Renderer
 */
class Textarea extends RendererAbstract
{
    /**
     * Default number of rows
     */
    const DEFAULT_ROWS = 5;

    /**
     * Default number of columns
     */
    const DEFAULT_COLS = 15;

    /**
     * @var string
     */
    protected $_template = 'customfield/input/renderer/textarea.phtml';

    /**
     * @var array
     */
    protected $classNames = ['textarea'];

    /**
     * @return int
     */
    public function getRows()
    {
        if (!$this->hasData('rows')) {
            $this->setData('rows', self::DEFAULT_ROWS);
        }
        return $this->getData('rows');
    }

    /**
     * @return int
     */
    public function getCols()
    {
        if (!$this->hasData('cols')) {
            $this->setData('cols', self::DEFAULT_COLS);
        }
        return $this->getData('cols');
    }
}
