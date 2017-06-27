<?php
/*////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\  Customer Approve/Disapprove 2.0 \\\\\\\\\\\\\\\\\\\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ NOTICE OF LICENSE\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                                                                   ///////
 \\\\\\\ This source file is subject to the Open Software License (OSL 3.0)\\\\\\\
 ///////   that is bundled with this package in the file LICENSE.txt.      ///////
 \\\\\\\   It is also available through the world-wide-web at this URL:    \\\\\\\
 ///////          http://opensource.org/licenses/osl-3.0.php               ///////
 \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                      * @category   EH                            ///////
 \\\\\\\                      * @package    EH_CustomerApprove             \\\\\\\
 ///////    * @author     Extensionhut <info@extensionhut.com>             ///////
 \\\\\\\                                                                   \\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\\\\\* @copyright  Copyright 2016 © www.extensionhut.com All right reserved\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 */
  
namespace EH\CustomerApprove\Setup;
 
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
 
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    private $_pageFactory;

    protected $customerSetupFactory;

    private $_attributeSetFactory;
    
    public function __construct(
		PageFactory $_pageFactory,
		CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $_attributeSetFactory
		) {
        $this->_pageFactory = $_pageFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->_attributeSetFactory = $_attributeSetFactory;
    }
    
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
		$data = [
                'title' => 'Account Awaiting Approval',
                'page_layout' => '1column',
                'identifier' => 'account-awaiting-approval',
                'content_heading' => 'Account Awaiting Approval',
                'content' => "<p>Your account has been created but needs to be approved by an administrator before you can sign in.</p>\r\n<p>An e-mail will be sent to the e-mail address you used when you registered this account once it has been approved.</p>\r\n<p>Thank you for your patience.</p>",
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ];
        $this->createPage()->setData($data)->save();
        
         /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->_attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        
        $customerSetup->addAttribute(Customer::ENTITY, 'eh_is_approved', [
            'type' => 'static',
            'label' => 'Is Approved?',
            'input' => null,
            'required' => false,
            'visible' => false,
            'user_defined' => true,
            'sort_order' => 1000,
            'position' => 1000,
            'system' => 0,
        ]);
        
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'eh_is_approved')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer'],
        ]);
        
        $attribute->save();
    }
    
    public function createPage() {
        return $this->_pageFactory->create();
    }
}
