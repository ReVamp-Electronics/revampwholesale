<?php

namespace IWD\SalesRep\Controller\Adminhtml\Reports;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class exportSalesExcel
 * @package IWD\SalesRep\Controller\Adminhtml\Reports
 */
class ExportSalesExcel extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Export sales report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'sales.xml';
        $grid = $this->_view->getLayout()->createBlock('\IWD\SalesRep\Block\Adminhtml\Reports\Order\Grid');
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getExcelFile(), DirectoryList::VAR_DIR);
    }
}
