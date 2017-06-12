<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

namespace Amasty\Pgrid\Ui\Component;

class Columns extends \Magento\Ui\Component\AbstractComponent
{
    const NAME = 'amasty_columns';

    public function getComponentName()
    {
        return static::NAME;
    }

    public function prepare()
   {
       $amasty = $this->getContext()->getRequestParam('amasty');

       if (isset($amasty['columns'])){
           $configData = $this->getContext()->getDataProvider()->getConfigData();
           $configData['amasty_columns'] = $amasty['columns'];

           $this->getContext()->getDataProvider()->setConfigData($configData);
       }

       $config = $this->getConfig();

       parent::prepare();
   }
}
