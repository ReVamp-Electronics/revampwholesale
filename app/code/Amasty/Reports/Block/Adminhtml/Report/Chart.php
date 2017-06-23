<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Webapi\Exception;

class Chart extends \Magento\Backend\Block\Template
{
    protected $collection;

    /**
     * Chart constructor.
     *
     * @param Context $context
     * @param array                                   $data
     *
     * @throws Exception
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        
        if (!isset($data['collection'])) // TODO check with "instanceof"
            throw new Exception(__('Collection is not specified for chart block'));
        
        $this->collection = $data['collection'];
        
        $this->collection->prepareCollection($this->collection);
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function getAxisFields()
    {
        $x = 'total_orders';
        $y = 'period';
        $filters = $this->getRequest()->getParam('amreports');
        $group = isset($filters['type']) ? $filters['type'] : 'overview';
        switch ($group) {
            case 'overview':
                $y = 'period';
                break;
            case 'status':
                $y = 'status';
                break;
        }

        $group = isset($filters['value']) ? $filters['value'] : 'quantity';
        switch ($group) {
            case 'quantity':
                $x = 'total_orders';
                break;
            case 'total':
                $x = 'total';
                break;
        }

        return ['x' => $x, 'y' => $y];
    }

    public function isDate()
    {
        $filters = $this->getRequest()->getParam('amreports');
        $group = isset($filters['type']) ? $filters['type'] : 'overview';
        $group == 'status' ? $isDate = false : $isDate = true;
        return $isDate;
    }
}
