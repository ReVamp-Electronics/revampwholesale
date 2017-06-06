<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block;

use Magento\Store\Model\ScopeInterface;

/**
 * Class FooterLink
 * @package Aheadworks\Rma\Block
 */
class FooterLink extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * FooterLink constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        if (!isset($data['label'])) {
            $data['label'] = __('Create New Return');
        }
        if (!isset($data['path'])) {
            if ($customerSession->isLoggedIn()) {
                $data['path'] = 'aw_rma/customer/index';
            } else {
                $allowGuestRma = (bool)$context->getScopeConfig()
                    ->getValue(
                        'aw_rma/general/allow_guest_requests',
                        ScopeInterface::SCOPE_STORE
                    );
                $data['path'] = $allowGuestRma ? 'aw_rma/guest/index' : 'customer/account/login';
            }
        }
        parent::__construct($context, $defaultPath, $data);
    }
}
