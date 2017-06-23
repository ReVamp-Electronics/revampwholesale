<?php
namespace Evdpl\Faques\Block\Adminhtml;

class Faq extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_template = 'faq/questions.phtml';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */

        protected $_fileFactory;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Evdpl\Faques\Block\Adminhtml\Faq\Grid', 'faques.question.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @return array
     */
    protected function _getAddButtonOptions()
    {

        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];

        return $splitButtonOptions;
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'faques/*/new'
        );
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
    
}