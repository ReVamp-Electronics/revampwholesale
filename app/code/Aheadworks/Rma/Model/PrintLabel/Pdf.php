<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\PrintLabel;

use Magento\Framework\App\Filesystem\DirectoryList;
use Aheadworks\Rma\Model\Source\CustomField\Refers;
use Aheadworks\Rma\Model\Source\CustomField\Type;

/**
 * Class Pdf
 * @package Aheadworks\Rma\Model\PrintLabel
 */
class Pdf
{
    const X_OFFSET = 25;

    const BOTTOM_OFFSET = 25;

    const CHARSET = 'UTF-8';

    const COLUMN_WIDTH = 300;

    const ITEMS_TABLE_OFFSET = 10;

    const ITEMS_CUSTOM_FIELDS_OFFSET = 10;

    /**
     * @var int
     */
    protected $y;

    /**
     * @var \Zend_Pdf
     */
    protected $pdf;

    /**
     * @var \Zend_Pdf_Page
     */
    protected $page;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
     protected $scopeConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $rootDirectory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\CustomField\CollectionFactory
     */
    protected $customFieldCollectionFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Aheadworks\Rma\Model\ResourceModel\CustomField\CollectionFactory $customFieldCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Escaper $escaper,
        \Aheadworks\Rma\Model\ResourceModel\CustomField\CollectionFactory $customFieldCollectionFactory
    ) {
        $this->localeDate = $localeDate;
        $this->scopeConfig = $scopeConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
        $this->escaper = $escaper;
        $this->customFieldCollectionFactory = $customFieldCollectionFactory;
    }

    /**
     * @param \Aheadworks\Rma\Model\Request $request
     * @return string
     */
    public function getPdf(\Aheadworks\Rma\Model\Request $request)
    {
        $this->pdf = new \Zend_Pdf();
        $this->y = 800;
        $this->page = $this->pdf->newPage(\Zend_Pdf_Page::SIZE_A4);

        $this
            ->insertLogo()
            ->insertHeadline(__('RMA %1', $request->getIncrementId()))
            ->insertDate($request->getCreatedAt())
        ;

        $y = $this->y;
        $this->insertAddress($request->getPrintLabel());
        $yAddress = $this->y;
        $this->y = $y;
        $this->insertDetails($request);
        $yDetails = $this->y;
        $this->y = min($yAddress, $yDetails);

        $this
            ->insertAdditionalInfo($request->getPrintLabel())
            ->insertItems($request->getItemsCollection(), $request->getStoreId())
        ;

        $this->pdf->pages[] = $this->page;
        return $this->pdf->render();
    }

    /**
     * @param $value
     */
    protected function deltaY($value)
    {
        $this->y -= $value;
        if ($this->y < self::BOTTOM_OFFSET) {
            $font = $this->page->getFont();
            $fontSize = $this->page->getFontSize();
            $this->pdf->pages[] = $this->page;
            $this->y = 800;
            $this->page = $this->pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
            $this->page->setFont($font, $fontSize);
        }
    }

    /**
     * @param $text
     * @param $x
     * @param string $charset
     * @param null $blockLen
     * @param int $yStep
     */
    protected function drawText($text, $x, $charset = '', $blockLen = null, $yStep = 10)
    {
        if (!$blockLen) {
            $this->page->drawText($text, $x, $this->y, self::CHARSET);
        } else {
            $text = wordwrap($text, $blockLen, "\n");
            $count = 0;
            $lines = explode("\n", $text);
            foreach ($lines as $line) {
                $this->page->drawText($line, $x, $this->y, $charset);
                if (++$count < count($lines)) {
                    $this->deltaY($yStep);
                }
            }
        }
    }

    /**
     * @return $this
     */
    protected function insertLogo()
    {
        // todo
        return $this;
    }

    /**
     * @param $text
     * @return $this
     */
    protected function insertHeadline($text)
    {
        $this->setFontRegular(18);
        $this->drawText((string)$text, self::X_OFFSET, self::CHARSET);
        $this->deltaY(24);
        return $this;
    }

    /**
     * @param $date
     * @return $this
     */
    protected function insertDate($date)
    {
        $this->setFontRegular(10);
        $this->page->drawText(
            __('Date') . ': ' .
            $this->localeDate->formatDate(
                $this->localeDate->scopeDate(null, $date, true),
                \IntlDateFormatter::SHORT,
                false
            ),
            self::X_OFFSET, $this->y, self::CHARSET
        );
        $this->deltaY(2);
        return $this;
    }

    /**
     * @param array $addressData
     * @return $this
     */
    protected function insertAddress($addressData)
    {
        $textBlockLen = 50;
        $this->page->setLineWidth(0.25);
        $this->page->drawLine(self::X_OFFSET, $this->y, 550, $this->y);
        $this->deltaY(20);

        $this->setFontRegular(18);
        $this->drawText((string)__('Return address'), self::X_OFFSET, self::CHARSET, $textBlockLen);
        $this->deltaY(20);

        $this->setFontRegular(12);
        $this->drawText(
            sprintf("%s %s", $addressData['firstname'], $addressData['lastname']),
            self::X_OFFSET, self::CHARSET, $textBlockLen
        );
        $this->deltaY(15);
        $this->drawText(
            str_replace('\n', ' ', $addressData['street']),
            self::X_OFFSET, self::CHARSET, $textBlockLen
        );
        $this->deltaY(15);
        $this->drawText(
            sprintf("%s, %s, %s", $addressData['city'], $this->getRegionName($addressData), $addressData['postcode']),
            self::X_OFFSET, self::CHARSET, $textBlockLen
        );
        $this->deltaY(15);
        $this->drawText(
            $this->getCountryName($addressData),
            self::X_OFFSET, self::CHARSET, $textBlockLen
        );
        $this->deltaY(15);
        $this->drawText($addressData['telephone'], self::X_OFFSET, self::CHARSET, $textBlockLen);

        return $this;
    }

    /**
     * @param \Aheadworks\Rma\Model\Request $request
     * @return $this
     */
    protected function insertDetails(\Aheadworks\Rma\Model\Request $request)
    {
        $textBlockLen = 20;
        $this->deltaY(20);
        $this->setFontRegular(18);
        $this->drawText((string)__('Details'), self::X_OFFSET + self::COLUMN_WIDTH, self::CHARSET, $textBlockLen);
        $this->deltaY(20);

        $this->setFontRegular(12);
        $this->drawText((string)__('Order ID') . ':', self::X_OFFSET + self::COLUMN_WIDTH, self::CHARSET, $textBlockLen);
        $this->drawText(
            '#' . $request->getOrder()->getIncrementId(),
            self::X_OFFSET + self::COLUMN_WIDTH * (1 + 0.4), self::CHARSET, $textBlockLen
        );
        $this->deltaY(12);

        foreach ($this->getRequestCustomFieldValues($request) as $name => $value) {
            $this->drawText($name . ':', self::X_OFFSET + self::COLUMN_WIDTH, self::CHARSET, $textBlockLen);
            $this->drawMultiLineText($value, self::X_OFFSET + self::COLUMN_WIDTH * (1 + 0.4), 12, $textBlockLen);
        }
        return $this;
    }

    /**
     * @param array $addressData
     * @return $this
     */
    protected function insertAdditionalInfo($addressData)
    {
        if (isset($addressData['additionalinfo']) && !empty($addressData['additionalinfo'])) {
            $this->deltaY(24);
            $this->setFontRegular(12);
            $this->drawMultiLineText($addressData['additionalinfo'], self::X_OFFSET, 12, 120);
        }
        return $this;
    }

    /**
     * @param \Aheadworks\Rma\Model\ResourceModel\RequestItem\Collection $itemsCollection
     * @param int $storeId
     * @return $this
     */
    protected function insertItems($itemsCollection, $storeId)
    {
        $this->deltaY(24);
        $this->setFontRegular(18);
        $this->drawText((string)__('Items RMA requested for'), self::X_OFFSET, self::CHARSET);
        $this->deltaY(24);

        $this->setFontBold(12);
        $columns = [
            'name' => ['caption' => 'Product Name', 'width' => 380],
            'sku' => ['caption' => 'SKU', 'width' => 80],
            'qty' => ['caption' => 'Qty', 'width' => 60],
        ];

        $offset = self::X_OFFSET + self::ITEMS_TABLE_OFFSET;
        foreach ($columns as $column) {
            $this->drawText((string)__($column['caption']), $offset, self::CHARSET, $column['width']);
            $offset += $column['width'];
        }

        $this->setFontRegular(12);
        $this->deltaY(10);
        $this->page->setLineWidth(0.1);
        $this->page->drawLine(self::X_OFFSET, $this->y, 540, $this->y);
        $this->deltaY(10);

        foreach ($itemsCollection as $item) {
            $this->deltaY(10);
            $offset = self::X_OFFSET + self::ITEMS_TABLE_OFFSET;
            foreach ($columns as $fieldName => $column) {
                $this->drawText($item->getData($fieldName), $offset, self::CHARSET, $column['width']);
                $offset += $column['width'];
            }
            $this->deltaY(12);

            $customFieldValueBlockLen = 80;
            foreach ($this->getItemCustomFieldValues($item, $storeId) as $name => $value) {
                $offset = self::X_OFFSET + self::ITEMS_TABLE_OFFSET + self::ITEMS_CUSTOM_FIELDS_OFFSET;
                $this->setFontBold(11);
                $this->drawText($name . ':', $offset, self::CHARSET, $customFieldValueBlockLen);
                $this->deltaY(12);
                $this->setFontRegular(11);
                $offset += self::ITEMS_CUSTOM_FIELDS_OFFSET;
                $this->drawMultiLineText($value, $offset, 12, $customFieldValueBlockLen);
            }

            $this->page->drawLine(self::X_OFFSET, $this->y, 540, $this->y);
            $this->deltaY(10);
            $this->setFontRegular(12);
        }

        return $this;
    }

    /**
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function setFontRegular($size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
        );
        $this->page->setFont($font, $size);
        return $font;
    }

    /**
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function setFontBold($size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf')
        );
        $this->page->setFont($font, $size);
        return $font;
    }

    /**
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function setFontItalic($size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_It-2.8.2.ttf')
        );
        $this->page->setFont($font, $size);
        return $font;
    }

    /**
     * @param $text
     * @param $offset
     * @param $deltaY
     * @param null $blockLen
     */
    protected function drawMultiLineText($text, $offset, $deltaY, $blockLen = null)
    {
        foreach (explode("\r\n", $text) as $str) {
            $this->drawText(strip_tags(ltrim($str)), $offset, self::CHARSET, $blockLen);
            $this->deltaY($deltaY);
        }
    }

    /**
     * @param array $addressData
     * @return string
     */
    protected function getRegionName($addressData)
    {
        if (isset($addressData['region_id'])) {
            $region = $this->regionFactory->create()
                ->load($addressData['region_id'])
            ;
            if ($region->getCountryId() == $addressData['country_id']) {
                return $region->getName();
            }
        }
        return isset($addressData['region']) ? $addressData['region'] : '';
    }

    /**
     * @param $addressData
     * @return string
     */
    protected function getCountryName($addressData)
    {
        $country = $this->countryFactory->create()
            ->load($addressData['country_id'])
        ;
        return $country->getName();
    }

    /**
     * @param \Aheadworks\Rma\Model\Request $request
     * @return array
     */
    protected function getRequestCustomFieldValues(\Aheadworks\Rma\Model\Request $request)
    {
        /** @var \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection $customFieldCollection */
        $customFieldCollection = $this->customFieldCollectionFactory->create()
            ->addRefersToFilter(Refers::REQUEST_VALUE)
            ->addDisplayInLabelFilter(true)
            ->joinAttributesValues(['frontend_label'], $request->getStoreId())
            ->setStoreId($request->getStoreId())
        ;
        return $this->collectCustomFieldValues($request, $customFieldCollection);
    }

    /**
     * @param \Aheadworks\Rma\Model\RequestItem $requestItem
     * @param int $storeId
     * @return array
     */
    protected function getItemCustomFieldValues(\Aheadworks\Rma\Model\RequestItem $requestItem, $storeId)
    {
        /** @var \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection $customFieldCollection */
        $customFieldCollection = $this->customFieldCollectionFactory->create()
            ->addRefersToFilter(Refers::ITEM_VALUE)
            ->addDisplayInLabelFilter(true)
            ->joinAttributesValues(['frontend_label'], $storeId)
            ->setStoreId($storeId)
        ;
        return $this->collectCustomFieldValues($requestItem, $customFieldCollection);
    }

    /**
     * @param \Aheadworks\Rma\Model\RequestItem|\Aheadworks\Rma\Model\Request $instance
     * @param \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection $customFieldCollection
     * @return array
     */
    protected function collectCustomFieldValues($instance, $customFieldCollection)
    {
        $result = [];
        foreach ($customFieldCollection as $customField) {
            $value = $instance->getCustomFieldValue($customField->getId());
            if (!$value) {
                continue;
            }
            if ($customField->getType() == Type::SELECT_VALUE) {
                $result[$customField->getFrontendLabel()] = $customField->getOptionLabelByValue($value);
            } elseif ($customField->getType() == Type::MULTI_SELECT_VALUE) {
                $labels = [];
                foreach ($value as $optionValue) {
                    $labels[] = $customField->getOptionLabelByValue($optionValue);
                }
                $result[$customField->getFrontendLabel()] = implode(', ', $labels);
            } else {
                $result[$customField->getFrontendLabel()] = $value;
            }
        }
        return $result;
    }
}
