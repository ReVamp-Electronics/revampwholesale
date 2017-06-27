<?php

namespace IWD\MultiInventory\Controller\Adminhtml\Warehouses;

class Save extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $source = $this->saveSource();
            $this->saveSourceAddress($source);

            $this->messageManager->addSuccessMessage(__('The source has been saved.'));
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', ['id' => $source->getStockId(), '_current' => true]);
                return;
            }

            $this->_redirect('*/*/');
            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }

        $this->_redirect('*/*/edit', ['_current' => true]);
    }

    private function saveSource()
    {
        $stock = $this->getRequest()->getParam('stock');

        if ($stock && isset($stock['stock_id'])) {
            $source = $this->getSourceRepository()->get($stock['stock_id']);
        } else {
            $source = $this->getSource();
        }

        $source->setStockName($stock['stock_name']);
        $this->getSourceRepository()->save($source);

        return $source;
    }

    /**
     * @param $source \IWD\MultiInventory\Api\Data\SourceInterface
     */
    private function saveSourceAddress($source)
    {
        $address = $this->getRequest()->getParam('address');

        $sourceAddress = $this->getSourceAddressRepository()->getBySourceId($source->getStockId());

        !isset($address['street']) ?: $sourceAddress->setStreet($address['street']);
        !isset($address['city']) ?: $sourceAddress->setCity($address['city']);
        !isset($address['country_id']) ?: $sourceAddress->setCountryId($address['country_id']);
        !isset($address['region_id']) ?: $sourceAddress->setRegionId($address['region_id']);
        !isset($address['region']) ?: $sourceAddress->setRegion($address['region']);
        !isset($address['postcode']) ?: $sourceAddress->setPostcode($address['postcode']);

        $sourceAddress->setStockId($source->getStockId());
        $this->getSourceAddressRepository()->save($sourceAddress);
    }
}
