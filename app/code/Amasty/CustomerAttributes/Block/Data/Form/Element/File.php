<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Block\Data\Form\Element;

use Magento\Framework\UrlFactory;

class File extends \Magento\Customer\Block\Adminhtml\Form\Element\File
{
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    private $urlDecoder;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * File constructor.
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param array $data
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        UrlFactory $urlFactory
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper,
            $adminhtmlData, $assetRepo, $urlEncoder, $data
        );
        $this->objectManager = $objectManager;
        $this->urlModel = $urlFactory->create();
        $this->customerSession = $customerSession;
    }

    public function getElementHtml()
    {
        if ($this->getValue() && strpos($this->getValue(), ".") !== false) {
            return $this->_getPreviewHtml() . '   ' . $this->_getHiddenInput() . $this->_getDeleteCheckboxHtml();
        }

        return parent::getElementHtml();
    }

    /**
     * Return File preview link HTML
     *
     * @return string
     */
    protected function _getPreviewHtml()
    {
        $html = '';
        if ($this->getValue() && !is_array($this->getValue()) && strpos($this->getValue(), ".") !== false) {
            $image = [
                'alt' => __('Download'),
                'title' => __('Download'),
                'src' => $this->_assetRepo->getUrl('Amasty_CustomerAttributes::images/fam_bullet_disk.gif'),
                'class' => 'v-middle'
            ];

            $url = $this->_getPreviewUrl();
            $html .= '<span>';
            $html .= '<a href="' . $url . '">' . $this->_drawElementHtml('img', $image) . '</a> ';
            $html .= '<a href="' . $url . '">' . __('Download') . '</a>';
            $html .= '</span>';
        }
        return $html;
    }

    /**
     * Return Preview/Download URL
     *
     * @return string
     */
    protected function _getPreviewUrl()
    {
        $customerId = $this->customerSession->getCustomer() ? $this->customerSession->getCustomer()->getId() : 0;
        return $this->urlModel->getUrl(
            'amcustomerattr/index/viewfile',
            [
                'file' => $this->urlEncoder->encode($this->getValue()),
                'customer_id' => $customerId
            ]
        );
    }
}
