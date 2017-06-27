<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml;

class Navigation extends \Magento\Backend\Block\Template
{

    public $title;

    public function getConfig()
    {
        return [
            'dashboard' => [
                'title'     => __('Dashboard'),
                'url'       => 'amasty_reports/report',
                'children'  => [],
                'resource'  => 'Amasty_Reports::reports'
            ],
            'sales' => [
                'title'     => __('Sales'),
                'resource'  => 'Amasty_Reports::reports_sales',
                'children'  => [
                    'overview' => [
                        'title' => __('Overview'),
                        'url' => 'amasty_reports/report_sales/overview',
                        'resource'  => 'Amasty_Reports::reports_sales_overview'
                    ],
                    'orders' => [
                        'title' => __('Orders'),
                        'url' => 'amasty_reports/report_sales/orders',
                        'resource'  => 'Amasty_Reports::reports_sales_orders'
                    ],
                    'by_hour' => [
                        'title' => __('Sales By Hour'),
                        'url' => 'amasty_reports/report_sales/hour',
                        'resource'  => 'Amasty_Reports::reports_sales_hour'
                    ],
                    'by_day' => [
                        'title' => __('Sales By Day of Week'),
                        'url' => 'amasty_reports/report_sales/weekday',
                        'resource'  => 'Amasty_Reports::reports_sales_weekday'
                    ],
                    'by_country' => [
                        'title' => __('Sales By Country'),
                        'url' => 'amasty_reports/report_sales/country',
                        'resource'  => 'Amasty_Reports::reports_sales_country'
                    ],
                    'by_payment' => [
                        'title' => __('Sales By Payment Type'),
                        'url' => 'amasty_reports/report_sales/payment',
                        'resource'  => 'Amasty_Reports::reports_sales_payment'
                    ],
                    'by_group' => [
                        'title' => __('Sales By Customer Group'),
                        'url' => 'amasty_reports/report_sales/group',
                        'resource'  => 'Amasty_Reports::reports_sales_group'
                    ],
                    'by_coupon' => [
                        'title' => __('Sales By Coupon'),
                        'url' => 'amasty_reports/report_sales/coupon',
                        'resource'  => 'Amasty_Reports::reports_sales_coupon'
                    ],
                    'by_category' => [
                        'title' => __('Sales By Category'),
                        'url' => 'amasty_reports/report_sales/category',
                        'resource'  => 'Amasty_Reports::reports_sales_category'
                    ],
                ]
            ],
            'catalog' => [
                'title'     => __('Catalog'),
                'resource'  => 'Amasty_Reports::reports_catalog',
                'children'  => [
                    'by_product' => [
                        'title' => __('By Product'),
                        'url' => 'amasty_reports/report_catalog/byProduct',
                        'resource'  => 'Amasty_Reports::reports_catalog_by_product'
                    ],
                    'bestsellers' => [
                        'title' => __('Bestsellers'),
                        'url' => 'amasty_reports/report_catalog/bestsellers',
                        'resource'  => 'Amasty_Reports::reports_catalog_bestsellers'
                    ],
                    'by_attributes' => [
                        'title' => __('By Product Attributes'),
                        'url' => 'amasty_reports/report_catalog/byAttributes',
                        'resource'  => 'Amasty_Reports::reports_catalog_by_attributes'
                    ],
                ]
            ],
            'customers' => [
                'title'     => __('Customers'),
                'resource'  => 'Amasty_Reports::reports_customers',
                'children'  => [
                    'customers' => [
                        'title' => __('Customers'),
                        'url' => 'amasty_reports/report_customers/customers',
                        'resource'  => 'Amasty_Reports::reports_customers_customers'
                    ],
                    'returning' => [
                        'title' => __('New vs Returning Customers'),
                        'url' => 'amasty_reports/report_customers/returning',
                        'resource'  => 'Amasty_Reports::reports_customers_returning'
                    ],
                ]
            ],
        ];
    }

    public function getCurrentTitle()
    {
        if (!$this->title)
        {
            $config = $this->getConfig();
            foreach ($config as $groupKey => &$group) {
                foreach ($group['children'] as $childKey => &$child) {
                    if (isset($child['url']) && $this->isUrlActive($child['url'])) {
                        $this->title = $child['title'];
                    }
                }
            }
        }
        return $this->title;
    }

    public function getMenu()
    {
        $config = $this->getMenuConfig();

        foreach ($config as $groupKey => &$group) {
            if (isset($group['resource']) && !$this->_authorization->isAllowed($group['resource'])) {
                unset($config[$groupKey]);
                continue;
            }

            if (isset($group['url']) && $this->isUrlActive($group['url'])) {
                $group['active'] = true;
            }

            foreach ($group['children'] as $childKey => &$child) {
                if (isset($child['resource']) && !$this->_authorization->isAllowed($child['resource'])) {
                    unset($group['children'][$childKey]);
                    continue;
                }

                if (isset($child['url']) && $this->isUrlActive($child['url'])) {
                    $this->title = $child['title'];
                    $child['active'] = true;
                    $group['active'] = true;
                }
            } unset($child);
        }

        return $config;
    }

    protected function isUrlActive($url)
    {
        $url = $this->normalizeUrl($url);

        return (false !== strpos($this->getRequest()->getPathInfo(), "/$url/"));
    }

    protected function normalizeUrl($url)
    {
        $parts = explode('/', $url);

        while (sizeof($parts) < 3) {
            $parts []= 'index';
        }

        return implode('/', $parts);
    }
}
