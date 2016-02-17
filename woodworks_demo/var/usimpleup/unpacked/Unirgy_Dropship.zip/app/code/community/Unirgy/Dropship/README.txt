===== USEFUL LINKS: =====

Extension home page:
http://www.magentocommerce.com/extension/reviews/module/1015

Documentation forum thread:
http://www.magentocommerce.com/boards/viewthread/35564

===== UNINSTALL INSTRUCTIONS: =====

If you delete extension files, run this statement on your MySQL database:

DELETE FROM `eav_attribute` where `attribute_code` like 'udropship_%';

===== COMPATIBILITY NOTES =====

* Unirgy_DropshipSplit is incompatible with Unirgy_DropshipMulti and Mage_GoogleCheckout.
* [Shopcart Price Rules > Free Shipping] should be only for matching items.
* [Shopcart Price Rules > Product Attribute > Dropship Vendor] will use udropship_vendor attribute also when Unirgy_DropshipMulti is enabled, instead of vendor chosen by decision logic.