<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Traits;

trait Filters {

    public function addFromFilter($collection, $tablePrefix = 'main_table')
    {
        $filters = $this->request->getParam('amreports');
        $from = isset($filters['from']) ? $filters['from'] : date('Y-m-d', $this->helper->getDefaultFromDate());
        if ($from) {
            $from = $this->helper->convertTime($from);
            $collection->getSelect()->where('DATE('.$tablePrefix.'.created_at) >= ?', $from);
        }
    }

    public function addToFilter($collection, $tablePrefix = 'main_table')
    {
        $filters = $this->request->getParam('amreports');
        $to = isset($filters['to']) ? $filters['to'] : date('Y-m-d');
        if ($to) {
            $to = $this->helper->convertTime($to);
            $collection->getSelect()->where('DATE('.$tablePrefix.'.created_at) <= ?', $to);
        }
    }

    public function addStoreFilter($collection, $tablePrefix = 'main_table')
    {
        $filters = $this->request->getParam('amreports');
        $store = isset($filters['store']) ? $filters['store'] : false;
        if ($store) {
            $collection->getSelect()->where($tablePrefix.'.store_id = ?', $store);
        }
    }

    public function addInterval($collection, $dateFiled = 'created_at', $tablePrefix = 'main_table')
    {
        $filters = $this->request->getParam('amreports');
        $interval = isset($filters['interval']) ? $filters['interval'] : 'day';
        switch ($interval) {
            case 'year';
                $collection->getSelect()
                    ->columns([
                        'period' => "YEAR($dateFiled)",
                    ])
                    ->group("YEAR($tablePrefix.$dateFiled)")
                ;
                break;
            case 'month';
                $collection->getSelect()
                    ->columns([
                        'period' => "CONCAT(YEAR($tablePrefix.$dateFiled), '-', MONTH($tablePrefix.$dateFiled))",
                    ])
                    ->group("MONTH($tablePrefix.$dateFiled)")
                ;
                break;
            case 'week';
                $collection->getSelect()
                    ->columns([
                        'period' => "CONCAT(ADDDATE(DATE($tablePrefix.$dateFiled), INTERVAL 1-DAYOFWEEK($tablePrefix.$dateFiled) DAY), ' - ', ADDDATE(DATE($tablePrefix.$dateFiled), INTERVAL 7-DAYOFWEEK($tablePrefix.$dateFiled) DAY))",
                    ])
                    ->group("WEEK($tablePrefix.$dateFiled)")
                ;
                break;
            case 'day':
            default:
                $collection->getSelect()
                    ->columns([
                        'period' => "DATE($tablePrefix.$dateFiled)",
                    ])
                    ->group('DATE('.$tablePrefix.'.'.$dateFiled.')')
                ;
        }
    }
    
    public function createUniqueEntity()
    {
        $filters = $this->request->getParam('amreports');
        $from = isset($filters['from']) ? $filters['from'] : date('Y-m-d', $this->helper->getDefaultFromDate());
        $to = isset($filters['to']) ? $filters['to'] : false;
        $store = isset($filters['store']) ? $filters['store'] : false;
        $interval = isset($filters['interval']) ? $filters['interval'] : 'day';
        $group = isset($filters['type']) ? $filters['type'] : 'overview';
        return md5($from.$to.$store.$interval.$group);
    }

}
