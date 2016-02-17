<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Block_Adminhtml_Vendor_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'udropship';
        $this->_controller = 'adminhtml_vendor';

        $this->_updateButton('save', 'label', Mage::helper('udropship')->__('Save Vendor'));
        $this->_updateButton('save_continue', 'label', Mage::helper('udropship')->__('Save and Continue Edit'));
        $this->_addButton('save_continue', array(
            'label'     => Mage::helper('udropship')->__('Save and Continue Edit'),
            'onclick'   => '$(\'save_continue\').value=1; editForm.submit();',
            'class'     => 'save',
        ), 10);
        $this->_updateButton('delete', 'label', Mage::helper('udropship')->__('Delete Vendor'));

        if( $this->getRequest()->getParam($this->_objectId) ) {
            $model = Mage::getModel('udropship/vendor')
                ->load($this->getRequest()->getParam($this->_objectId));
            Mage::register('vendor_data', $model);
        } elseif (($sessVD = Mage::getSingleton('adminhtml/session')->getData('uvendor_edit_data', true))) {
        	unset($sessVD['logo']);
            if (Mage::registry('vendor_data')) {
                Mage::registry('vendor_data')->setData($sessVD);
            } else {
                Mage::register('vendor_data', Mage::getModel('udropship/vendor')->setData($sessVD));
            }
        }
#var_dump(Mage::registry('vendor_data')); exit;
    }

    public function getHeaderText()
    {
        if( Mage::registry('vendor_data') && Mage::registry('vendor_data')->getId() ) {
            return Mage::helper('udropship')->__("Edit Vendor '%s'", $this->htmlEscape(Mage::registry('vendor_data')->getVendorName()));
        } else {
            return Mage::helper('udropship')->__('New Vendor');
        }
    }

    public function getFormScripts()
    {
        ob_start();
        $vendor = Mage::registry('vendor_data');

        $collection = Mage::helper('udropship')->getShippingMethods();
        $carriers = array();
        foreach ($collection as $s) {
            if (!$s->getSystemMethods()) {
                $carriers[$s->getId()] = array();
                continue;
            }
            foreach ($s->getSystemMethods() as $k=>$v) {
                $carriers[$s->getId()][$k] = $v;
            }
        }
?>
<script type="text/javascript">

var updater = new RegionUpdater('country_id', 'region', 'region_id', <?php echo $this->helper('directory')->getRegionJson() ?>, 'disable');
var bUpdater = new RegionUpdater('billing_country_id', 'billing_region', 'billing_region_id', <?php echo $this->helper('directory')->getRegionJson() ?>, 'disable');

function udSyncRegField(countrySel) {
    var regionIdRow = countrySel.up(1).next();
    var regionRow = countrySel.up(1).next(1);
    var regionIdSel = regionIdRow.select('select[name=region_id],select[name=billing_region_id]')
    console.log(regionIdSel)
    if (regionIdSel && (regionIdSel = regionIdSel[0])) {
        if (regionIdSel.disabled) {
            regionIdRow.hide();//.select('select,input').invoke('disable');
            regionRow.show();//.select('select,input').invoke('enable');
        } else {
            regionIdRow.show();//.select('select,input').invoke('enable');
            regionRow.hide();//.select('select,input').invoke('disable');
        }
    }
}
udSyncRegField($('country_id'));
if (!$F('billing_use_shipping')) {
    udSyncRegField($('billing_country_id'));
}
varienGlobalEvents.attachEventHandler("address_country_changed", udSyncRegField);

var allowedCarriers = <?php echo Zend_Json::encode($carriers) ?>;

if (typeof udropship_vendor_productsJsObject != "undefined") {
    if (!$('vendor_products').value) {
        $('vendor_products').value = '{}';
    }
    var vendorProducts = $('vendor_products').value.evalJSON();

    function changeVendorProductProperty() {
        if (!vendorProducts[this.productId]) {
            vendorProducts[this.productId] = {};
        }
        if (!this.name) {
            return;
        }
        var fname = this.name.replace(/^_/, '');
        vendorProducts[this.productId][fname] = this.value;
        highlightProductRow(this);
        $('vendor_products').value = Object.toJSON(vendorProducts);
    }

    function highlightProductRow(input, changed) {
        return; // disabled until done properly
        $(input).up('tr').select('td').each(function (el) {
            el.style.backgroundColor = changed || typeof changed=='undefined' ? '#ffb' : '';
        });
    }

    udropship_vendor_productsJsObject.initCallback = function(self) {
        self.initGridRows && self.initGridRows();
    }

    udropship_vendor_productsJsObject.initRowCallback = function(self, row) {
        var inputs = $(row).select('input', 'select'), id, selected, fname;
        for (var i=0; i<inputs.length; i++) {
            if (inputs[i].type=='checkbox' && inputs[i].name=='') {
                id = inputs[i].value;
                if (vendorProducts[id] && (typeof vendorProducts[id]['on'] !== 'undefined')) {
                    selected = vendorProducts[id]['on'];
                    inputs[i].checked = selected;
                    highlightProductRow(inputs[i]);
                } else {
                    selected = inputs[i].checked;
                }
            } else {
                inputs[i].disabled = !selected;
                inputs[i].productId = id;
                fname = inputs[i].name.replace(/^_/, '');
                if (vendorProducts[id] && vendorProducts[id][fname]) {
                    inputs[i].value = vendorProducts[id][fname];
                }
                $(inputs[i]).observe('change', changeVendorProductProperty);
            }
        }
    }

    udropship_vendor_productsJsObject.checkboxCheckCallback = function(grid, element, checked){
        $(element).up('tr').select('input', 'select').each(function (el) {
            if (el.type=='checkbox' && el.name=='') {
                if (!vendorProducts[el.value]) {
                    vendorProducts[el.value] = {};
                }
                vendorProducts[el.value]['on'] = checked;
                highlightProductRow(element);
            } else {
                el.disabled = !checked;
            }
        });
        $('vendor_products').value = Object.toJSON(vendorProducts);
    }

    udropship_vendor_productsJsObject.rowClickCallback = function(grid, event){
        var trElement = Event.findElement(event, 'tr');
        var isInput   = Event.element(event).tagName.match(/(input|select|option)/i);
        if(trElement){
            var checkbox = Element.getElementsBySelector(trElement, 'input');
            if(checkbox[0]){
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                udropship_vendor_productsJsObject.setCheckboxChecked(checkbox[0], checked);
            }
        }
    }
    udropship_vendor_productsJsObject.initGrid();
}

if (typeof udropship_vendor_shippingJsObject != "undefined") {
    if (!$('vendor_shipping').value) {
        $('vendor_shipping').value = '{}';
    }
    var vendorShipping = $('vendor_shipping').value.evalJSON();

    function changeVendorShippingProperty() {
        if (!vendorShipping[this.shippingId]) {
            vendorShipping[this.shippingId] = {};
        }
        if (!this.name) {
            return;
        }
        var fname = this.name.replace(/^_/, '');
        vendorShipping[this.shippingId][fname] = this.value;
        highlightProductRow(this);
        $('vendor_shipping').value = Object.toJSON(vendorShipping);
    }

    function highlightProductRow(input, changed) {
        return; // disabled until done properly
        $(input).up('tr').select('td').each(function (el) {
            el.style.backgroundColor = changed || typeof changed=='undefined' ? '#ffb' : '';
        });
    }

    udropship_vendor_shippingJsObject.initCallback = function(self) {
        self.initGridRows && self.initGridRows();
    }

    udropship_vendor_shippingJsObject.initRowCallback = function(self, row) {
        var inputs = $(row).select('input', 'select'), id, selected, fname;
        for (var i=0; i<inputs.length; i++) {
            if (inputs[i].type=='checkbox' && inputs[i].name=='') {
                id = inputs[i].value;
                if (vendorShipping[id] && (typeof vendorShipping[id]['on'] !== 'undefined')) {
                    selected = vendorShipping[id]['on'];
                    inputs[i].checked = selected;
                    highlightProductRow(inputs[i]);
                } else {
                    selected = inputs[i].checked;
                }
            } else {
                inputs[i].disabled = !selected;
                inputs[i].shippingId = id;
                fname = inputs[i].name.replace(/^_/, '');
                if (vendorShipping[id] && vendorShipping[id][fname]) {
                    inputs[i].value = vendorShipping[id][fname];
                }
                if (inputs[i].tagName.match(/select/i) && inputs[i].name.match(/carrier_code/i)) {
                    for (var j=0; j<inputs[i].options.length; j++) {
                        if (inputs[i].options[j].value && inputs[i].options[j].value!='**estimate**' && !allowedCarriers[id][inputs[i].options[j].value]) {
                            Element.remove(inputs[i].options[j]);
                            j--;
                        }
                    }
                }
                $(inputs[i]).observe('change', changeVendorShippingProperty);
            }
        }
    }

    udropship_vendor_shippingJsObject.checkboxCheckCallback = function(grid, element, checked){
        $(element).up('tr').select('input', 'select').each(function (el) {
            if (el.type=='checkbox' && el.name=='') {
                if (!vendorShipping[el.value]) {
                    vendorShipping[el.value] = {};
                }
                vendorShipping[el.value]['on'] = checked;
                highlightProductRow(element);
            } else {
                el.disabled = !checked;
            }
        });
        $('vendor_shipping').value = Object.toJSON(vendorShipping);
    }

    udropship_vendor_shippingJsObject.rowClickCallback = function(grid, event){
        var trElement = Event.findElement(event, 'tr');
        var isInput   = Event.element(event).tagName.match(/(input|select|option)/i);
        if(trElement){
            var checkbox = Element.getElementsBySelector(trElement, 'input');
            if(checkbox[0]){
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                udropship_vendor_shippingJsObject.setCheckboxChecked(checkbox[0], checked);
            }
        }
    }
    udropship_vendor_shippingJsObject.initGrid();
}

</script>
<?php
        return ob_get_clean();
    }
}
