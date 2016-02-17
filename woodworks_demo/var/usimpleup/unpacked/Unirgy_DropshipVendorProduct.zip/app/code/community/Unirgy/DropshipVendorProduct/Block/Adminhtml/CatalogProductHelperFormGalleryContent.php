<?php

class Unirgy_DropshipVendorProduct_Block_Adminhtml_CatalogProductHelperFormGalleryContent extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content
{
    protected function _beforeToHtml()
    {
        if ($this->isConfigurable()) {
            $this->setTemplate('udprod/catalogProductHelperGallery.phtml');
        }
        return parent::_beforeToHtml();
    }
    
    public function getProduct()
    {
        return Mage::registry('product')
            ? Mage::registry('product')
            : Mage::registry('current_product');
    }

    public function isConfigurable()
    {
        return $this->getProduct()->getTypeId() == 'configurable';
    }
    
    protected $_iiAttrs;
    public function getIdentifyImageAttributes()
    {
        if (is_null($this->_iiAttrs)) {
            $this->_iiAttrs = array();
            $p = $this->getProduct();
            foreach ($p->getTypeInstance(true)->getConfigurableAttributes($p) as $cfgAttr) {
                if ($cfgAttr->getIdentifyImage()) {
                    $this->_iiAttrs[] = $cfgAttr;
                    $availableValues = array();
                    $cPrices = $cfgAttr->getPrices();
                    if (!empty($cPrices)) {
                        foreach ($cfgAttr->getPrices() as $prEntry) {
                            $availableValues[$prEntry['value_index']] = $prEntry['label'];
                        }
                    }
                    $cfgAttr->setAvailableValues($availableValues);
                }
            }
        }
        return $this->_iiAttrs;
    }
    
    public function getIdentifyImageAttributesJson()
    {
        $iiAttrs = array();
        $p = $this->getProduct();
        foreach ($p->getTypeInstance()->getConfigurableAttributesAsArray($p) as $cfgAttr) {
            if ($cfgAttr['identify_image']) {
                $iiAttrs[] = $cfgAttr;
            }
        }
        return Mage::helper('core')->jsonEncode($iiAttrs);
    }

    protected function _afterToHtml($html)
    {
        $afterHtml = <<<EOT
<script type="text/javascript">
var hrefGalleryImages = function() {
    \$\$('#{$this->getHtmlId()} img').each(function(img){
        Element.wrap(img, 'a', {href: 'javascript:void(0)', onclick: "imagePreview($(this).down())"})
    })
}
if ((/msie [1-6]\./i).test(navigator.userAgent)) {
    Event.observe(window, 'load', hrefGalleryImages)
} else {
    document.observe("dom:loaded", hrefGalleryImages)
}
</script>
EOT;
        return $html.$afterHtml;
    }
 
}
