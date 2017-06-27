<?php

namespace MW\RewardPoints\Helper;

use Magento\Framework\Exception\LocalizedException;

class Image extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_model;
    protected $_scheduleResize = false;
    protected $_scheduleWatermark = false;
    protected $_scheduleRotate = false;
    protected $_angle;
    protected $_watermark;
    protected $_watermarkPosition;
    protected $_watermarkSize;
    protected $_imageFile;
    protected $_placeholder;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Model\Image
     */
    protected $_image;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param Data $dataHelper
     * @param \MW\RewardPoints\Model\Image $image
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\Image $image
    ) {
        parent::__construct($context);
        $this->_request = $request;
        $this->_logger = $logger;
        $this->_urlBuilder = $urlBuilder;
        $this->_assetRepo = $assetRepo;
        $this->_dataHelper = $dataHelper;
        $this->_image = $image;
    }

    /**
     * Reset all previos data
     */
    protected function _reset()
    {
        $this->_model             = null;
        $this->_scheduleResize    = false;
        $this->_scheduleWatermark = false;
        $this->_scheduleRotate    = false;
        $this->_angle             = null;
        $this->_watermark         = null;
        $this->_watermarkPosition = null;
        $this->_watermarkSize     = null;
        $this->_imageFile         = null;

        return $this;
    }

    public function init($imageFile)
    {
        $this->_reset();
        $this->_setModel($this->_image);
        $this->setImageFile($imageFile);

        return $this;
    }

    /**
     * Schedule resize of the image
     * $width *or* $height can be null - in this case, lacking dimension will be calculated.
     *
     * @see Mage_Catalog_Model_Product_Image
     * @param int $width
     * @param int $height
     * @return HM_EasyBanner_Helper_Image
     */
    public function resize($width, $height = null)
    {
        $this->_getModel()->setWidth($width)->setHeight($height);
        $this->_scheduleResize = true;

        return $this;
    }


    /**
     * Guarantee, that image picture width/height will not be distorted.
     * Applicable before calling resize()
     * It is true by default.
     *
     * @see Mage_Catalog_Model_Product_Image
     * @param bool $flag
     * @return HM_EasyBanner_Helper_Image
     */
    public function keepAspectRatio($flag)
    {
        $this->_getModel()->setKeepAspectRatio($flag);

        return $this;
    }

    /**
     * Guarantee, that image will have dimensions, set in $width/$height
     * Applicable before calling resize()
     * Not applicable, if keepAspectRatio(false)
     *
     * $position - TODO, not used for now - picture position inside the frame.
     *
     * @see Mage_Catalog_Model_Product_Image
     * @param bool $flag
     * @param array $position
     * @return HM_EasyBanner_Helper_Image
     */
    public function keepFrame($flag, $position = ['center', 'middle'])
    {
        $this->_getModel()->setKeepFrame($flag);

        return $this;
    }

    /**
     * Guarantee, that image will not lose transparency if any.
     * Applicable before calling resize()
     * It is true by default.
     *
     * $alphaOpacity - TODO, not used for now
     *
     * @see Mage_Catalog_Model_Product_Image
     * @param bool $flag
     * @param int $alphaOpacity
     * @return HM_EasyBanner_Helper_Image
     */
    public function keepTransparency($flag, $alphaOpacity = null)
    {
        $this->_getModel()->setKeepTransparency($flag);

        return $this;
    }

    /**
     * Guarantee, that image picture will not be bigger, than it was.
     * Applicable before calling resize()
     * It is false by default
     *
     * @param bool $flag
     * @return HM_EasyBanner_Helper_Image
     */
    public function constrainOnly($flag)
    {
        $this->_getModel()->setConstrainOnly($flag);

        return $this;
    }

    /**
     * Set color to fill image frame with.
     * Applicable before calling resize()
     * The keepTransparency(true) overrides this (if image has transparent color)
     * It is white by default.
     *
     * @param array $colorRGB
     * @return HM_EasyBanner_Helper_Image
     */
    public function backgroundColor($colorRGB)
    {
        // Assume that 3 params were given instead of array
        if (!is_array($colorRGB)) {
            $colorRGB = func_get_args();
        }
        $this->_getModel()->setBackgroundColor($colorRGB);

        return $this;
    }

    public function rotate($angle)
    {
        $this->setAngle($angle);
        $this->_getModel()->setAngle($angle);
        $this->_scheduleRotate = true;

        return $this;
    }

    public function watermark($fileName, $position, $size = null)
    {
        $this->setWatermark($fileName)
            ->setWatermarkPosition($position)
            ->setWatermarkSize($size);
        $this->_scheduleWatermark = true;

        return $this;
    }

    public function placeholder($fileName)
    {
        $this->_placeholder = $fileName;
    }

    public function getPlaceholder()
    {
        if (!$this->_placeholder) {
            $attr               = $this->_getModel()->getDestinationSubdir();
            $this->_placeholder = 'images/catalog/product/placeholder/' . $attr . '.jpg';
        }

        return $this->_placeholder;
    }

    public function __toString()
    {
        try {
            if ($this->getImageFile()) {
                $this->_getModel()->setBaseFile($this->getImageFile());
            }

            if ($this->_getModel()->isCached()) {
                return $this->_getModel()->getUrl();
            } else {
                if ($this->_scheduleRotate) {
                    $this->_getModel()->rotate($this->getAngle());
                }

                if ($this->_scheduleResize) {
                    $this->_getModel()->resize();
                }

                if ($this->_scheduleWatermark) {
                    $this->_getModel()
                        ->setWatermarkPosition($this->getWatermarkPosition())
                        ->setWatermarkSize($this->parseSize($this->getWatermarkSize()))
                        ->setWatermark($this->getWatermark(), $this->getWatermarkPosition());
                } else {
                    if ($watermark = $this->_dataHelper->getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_image")) {
                        $this->_getModel()
                            ->setWatermarkPosition($this->getWatermarkPosition())
                            ->setWatermarkSize($this->parseSize($this->getWatermarkSize()))
                            ->setWatermark($watermark, $this->getWatermarkPosition());
                    }
                }

                $url = $this->_getModel()->saveFile()->getUrl();
            }
        } catch (\Exception $e) {
            $url = $this->getViewFileUrl($this->getPlaceholder());
        }

        return $url;
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->_request->isSecure()], $params);
            return $this->_assetRepo->getUrlWithParams($fileId, $params);
        } catch (LocalizedException $e) {
            $this->_logger->critical($e);
            return $this->_urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }

    protected function _setModel($model)
    {
        $this->_model = $model;

        return $this;
    }

    protected function _getModel()
    {
        return $this->_model;
    }

    protected function setAngle($angle)
    {
        $this->_angle = $angle;

        return $this;
    }

    protected function getAngle()
    {
        return $this->_angle;
    }

    protected function setWatermark($watermark)
    {
        $this->_watermark = $watermark;

        return $this;
    }

    protected function getWatermark()
    {
        return $this->_watermark;
    }

    protected function setWatermarkPosition($position)
    {
        $this->_watermarkPosition = $position;

        return $this;
    }

    protected function getWatermarkPosition()
    {
        if ($this->_watermarkPosition) {
            return $this->_watermarkPosition;
        } else {
            return $this->_dataHelper->getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_position");
        }
    }

    public function setWatermarkSize($size)
    {
        $this->_watermarkSize = $size;

        return $this;
    }

    protected function getWatermarkSize()
    {
        if ($this->_watermarkSize) {
            return $this->_watermarkSize;
        } else {
            return $this->_dataHelper->getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_size");
        }
    }

    protected function setImageFile($file)
    {
        $this->_imageFile = $file;

        return $this;
    }

    protected function getImageFile()
    {
        return $this->_imageFile;
    }

    /**
     * @param $string
     * @return array|bool
     */
    protected function parseSize($string)
    {
        $size = explode('x', strtolower($string));
        if (sizeof($size) == 2) {
            return [
                'width'  => ($size[0] > 0) ? $size[0] : null,
                'heigth' => ($size[1] > 0) ? $size[1] : null,
            ];
        }

        return false;
    }
}
