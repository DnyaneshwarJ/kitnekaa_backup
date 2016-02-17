if (Product && Product.Config) {
    Product.Config.prototype.configureElement = Product.Config.prototype.configureElement.wrap(function(callOrig, element) {
        callOrig.call(this, element);
        if (!element.nextSetting) {
            try {
                window['unirgyProductVendors'+this.config.productId].superAttributesChanged(this);
                window['unirgyProductGaller'+this.config.productId].superAttributesChanged(this);
            } catch (e) {}
        }
    });
    Product.Config.prototype.getMatchingSimpleProduct = function(){
        var inScopeProductIds = this.getInScopeProductIds();
        if ((typeof inScopeProductIds != 'undefined') && (inScopeProductIds.length == 1)) {
            return inScopeProductIds[0];
        }
        return false;
    };
    Product.Config.prototype.getInScopeProductIds = function(optionalAllowedProducts) {

        var childProducts = this.config.childProducts;
        var allowedProducts = [];

        if ((typeof optionalAllowedProducts != 'undefined') && (optionalAllowedProducts.length > 0)) {
            // alert("starting with: " + optionalAllowedProducts.inspect());
            allowedProducts = optionalAllowedProducts;
        }

        for(var s=0, len=this.settings.length-1; s<=len; s++) {
            if (this.settings[s].selectedIndex <= 0){
                break;
            }
            var selected = this.settings[s].options[this.settings[s].selectedIndex];
            if (s==0 && allowedProducts.length == 0){
                allowedProducts = selected.config.allowedProducts;
            } else {
                // alert("merging: " + allowedProducts.inspect() + " with: " + selected.config.allowedProducts.inspect());
                allowedProducts = allowedProducts.intersect(selected.config.allowedProducts).uniq();
                // alert("to give: " + allowedProducts.inspect());
            }
        }

        //If we can't find any products (because nothing's been selected most likely)
        //then just use all product ids.
        if ((typeof allowedProducts == 'undefined') || (allowedProducts.length == 0)) {
            productIds = Object.keys(childProducts);
        } else {
            productIds = allowedProducts;
        }
        return productIds;
    };
}