<?php

namespace IWD\SalesRep\Helper\Plugin\B2B;

/**
 * Class Data
 * @package IWD\SalesRep\Helper\Plugin\B2B
 */
class Data
{
    use B2BHelperTrait;

    public function afterIsB2BUser($subject, $res)
    {
        if ($this->isB2BInstalled() && !$res) {
            if ($this->isSalesrepLoggedInAsCustomer()) {
                $fakeModel = $this->_b2bCustomer->create();
                $res = $fakeModel;
            }
        }

        return $res;
    }

    public function afterCheckB2BCustomerAccess($subject, $res)
    {
        if ($this->isB2BInstalled() && $res && is_array($res)) {
            if ($this->isSalesrepLoggedInAsCustomer()) {
                $res = false;
            }
        }

        return $res;
    }
}
