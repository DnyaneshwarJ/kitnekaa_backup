<?php

class UnirgyCustom_DropshipUsers_Model_Observer
{

    public function udropship_vendor_save_before($observer)
    {
        Mage::register('vendor_password',$observer->getVendor()->getPassword());
    }
    public function udropship_vendor_save_after($observer)
    {
        $vendor = $observer->getVendor();
        $role_id = Mage::getModel('admin/roles')->getCollection()
            ->addFieldToFilter('role_name', 'Seller')->getFirstItem()->getId();

        try {
            $user = Mage::getModel('admin/user')
                ->setData(array(
                    'username' => $vendor->getEmail(),
                    'firstname' => $vendor->getVendorName(),
                    'lastname' => $vendor->getVendorName(),
                    'email' => $vendor->getEmail(),
                    'password' => Mage::registry('vendor_password'),
                    'vendor_id'=>$vendor->getVendorId(),
                    'is_active' => 1
                ))->save();

        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
        //Assign Role Id
        try {
            $user->setRoleIds(array($role_id))//Administrator role id is 1 ,Here you can assign other roles ids
            ->setRoleUserId($user->getUserId())
                ->saveRelations();

        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }

    }

}