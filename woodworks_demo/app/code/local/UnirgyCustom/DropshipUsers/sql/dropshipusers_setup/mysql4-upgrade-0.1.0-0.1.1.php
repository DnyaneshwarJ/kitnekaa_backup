<?php
$installer = $this;
$installer->startSetup();
$roles = array('Seller');
$roleIds = array();
$resources = explode(',', '__root__,admin/sales,admin/sales/order,admin/sales/order/actions,admin/sales/order/actions/hold,admin/sales/order/actions/creditmemo,admin/sales/order/actions/unhold,admin/sales/order/actions/ship,admin/sales/order/actions/emails,admin/sales/order/actions/comment,admin/sales/order/actions/invoice,admin/sales/order/actions/capture,admin/sales/order/actions/email,admin/sales/order/actions/view,admin/sales/order/actions/reorder,admin/sales/order/actions/edit,admin/sales/order/actions/review_payment,admin/sales/order/actions/cancel,admin/sales/order/actions/create,admin/sales/invoice,admin/sales/shipment,admin/sales/creditmemo,admin/sales/udropship,admin/sales/udropship/vendor,admin/sales/udropship/shipping,admin/sales/udropship/label_batch,admin/sales/udropship/statement,admin/sales/udropship/udshipclass_customer,admin/sales/udropship/udshipclass_vendor,admin/sales/checkoutagreement,admin/sales/transactions,admin/sales/transactions/fetch,admin/sales/recurring_profile,admin/sales/billing_agreement,admin/sales/billing_agreement/actions,admin/sales/billing_agreement/actions/view,admin/sales/billing_agreement/actions/manage,admin/sales/billing_agreement/actions/use,admin/sales/tax,admin/sales/tax/classes_customer,admin/sales/tax/classes_product,admin/sales/tax/classes_vendor,admin/sales/tax/import_export,admin/sales/tax/rates,admin/sales/tax/rules,admin/quote2sales,admin/quote2sales/request,admin/quote2sales/quote,admin/quote2sales/quote/actions,admin/quote2sales/quote/actions/duplicate,admin/quote2sales/quote/actions/quotetoorder,admin/quote2sales/quote/actions/cancel,admin/quote2sales/quote/actions/edit,admin/quote2sales/quote/actions/create,admin/customer,admin/customer/manage');
foreach($roles as $role){
    $col = Mage::getModel('admin/role')->setRoleName($role)->setRoleType('G')->setTreeLevel(1)->save();
    if($col->getRoleId()){
       /* if( in_array($role, $roles) )
            $roleIds[] = $col->getRoleId();*/
        $rules = Mage::getModel('admin/rules')->setRoleId($col->getRoleId())->setResources($resources);
        $rules = Mage::getModel('admin/resource_rules')->saveRel($rules);
    }
}
$installer->endSetup();
	 