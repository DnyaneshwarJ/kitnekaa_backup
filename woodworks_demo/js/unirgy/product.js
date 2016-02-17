var UnirgyProductConfig = Class.create(Product.Config, {
    initialize: function($super, config, frontOptions) {
        this.frontOptions = {
            onlyLeftLimit: 20,
            onlyLeftMsg: 'Only #{qty} Left!',
            availableMsg: 'Available',
            outOfStockMsg: "We're all out",
            unirgyProductGallery: 'unirgyProductGallery',
            unirgyProductVendors: 'unirgyProductVendors',
            titlePrefix: '',
            saPrefix: '',
            noPreselect: false,
            usePerAttrChooseText: false
        }
        Object.extend(this.frontOptions, frontOptions)
        this.config     = config;
        this.taxConfig  = this.config.taxConfig;
        this.settings   = this.frontOptions.saSelectClass ? $$('.'+this.frontOptions.saSelectClass) : $$('.super-attribute-select');
        
        this.state      = new Hash();
        this.priceTemplate = new Template(this.config.template);
        this.prices     = config.prices;

        this.settings.each(function(element){
            Event.observe(element, 'change', this.setStateConfigure2.bindAsEventListener(this, element));
        }.bind(this));

        // fill state
        this.settings.each(function(element){
            var attributeId = element.id.replace(/[a-z]*/, '');
            if (this.frontOptions.saPrefix && attributeId.startsWith(this.frontOptions.saPrefix)) {
                attributeId = attributeId.substr(this.frontOptions.saPrefix.length)
            }
            if(attributeId && this.config.attributes[attributeId]) {
                element.config = this.config.attributes[attributeId];
                element.attributeId = attributeId;
                this.state[attributeId] = false;
            }
        }.bind(this))

        // Init settings dropdown
        var childSettings = [];
        for(var i=this.settings.length-1;i>=0;i--){
            var prevSetting = this.settings[i-1] ? this.settings[i-1] : false;
            var nextSetting = this.settings[i+1] ? this.settings[i+1] : false;
            if(i==0){
                this.fillSelect(this.settings[i])
            }
            else {
                this.settings[i].disabled=true;
            }
            $(this.settings[i]).childSettings = childSettings.clone();
            $(this.settings[i]).prevSetting   = prevSetting;
            $(this.settings[i]).nextSetting   = nextSetting;
            childSettings.push(this.settings[i]);
        }

        // try retireve options from url
        var separatorIndex = window.location.href.indexOf('#');
        if (separatorIndex!=-1) {
            var paramsStr = window.location.href.substr(separatorIndex+1);
            this.values = paramsStr.toQueryParams();
            this.settings.each(function(element){
                var attributeId = element.attributeId;
                element.value = (typeof(this.values[attributeId]) == 'undefined')? '' : this.values[attributeId];
                this.configureElement(element);
            }.bind(this));
        }
        this.settings.each(function(element){
            if (this.values && this.values[element.config.id]) this.state[element.config.id] = this.values[element.config.id]
            else if (!this.frontOptions.noPreselect) this.state[element.config.id] = element.config.defaultValueId
        }.bind(this))
        this.configureElement(this.settings[0])
    },
    configureElement: function($super, element) {
        var initFlag = false;
        if (!!this.state[element.config.id]) {
            for (var i=1; i<element.options.length; i++) {
                if (element.options[i].value == this.state[element.config.id]) {
                    element.value = this.state[element.config.id]
                    element.selectedIndex = i
                    initFlag = true
                    break
                }
            }
        }
        if (!initFlag && !!element.options[1] && !this.frontOptions.noPreselect) {
            //element.selectedIndex = 1
            this.state[element.config.id] = element.value = element.options[1].value
        }
        try {
        $super(element)
        } catch (e) {}
        this.markSelectedOption(element)
        if (element.nextSetting) {
            if (!this.frontOptions.noPreselect) this.configureElement(element.nextSetting)
        } else {
            try {
                window[this.frontOptions.unirgyProductGallery].superAttributesChanged(this);
            } catch (e) {}
            try {
                window[this.frontOptions.unirgyProductVendors].superAttributesChanged(this);
            } catch (e) {}
        }
    },
    fillSelect: function($super, element) {
        var attributeId = element.id.replace(/[a-z]*/, '');
        if (this.frontOptions.saPrefix && attributeId.startsWith(this.frontOptions.saPrefix)) {
            attributeId = attributeId.substr(this.frontOptions.saPrefix.length)
        }
        var options = this.getAttributeOptions(attributeId);
        this.clearSelect(element);
        var chooseText = this.config.chooseText;
        if (this.frontOptions.usePerAttrChooseText
            && this.config.perAttrChooseText
            && this.config.perAttrChooseText[attributeId]
        ) {
            chooseText = this.config.perAttrChooseText[attributeId];
        }
        element.options[0] = new Option(chooseText, '');

        var prevConfig = false;
        if(element.prevSetting){
            prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
        }

        if(options) {
            var index = 1;
            for(var i=0;i<options.length;i++){
                var allowedProducts = [];
                if(prevConfig) {
                    for(var j=0;j<options[i].products.length;j++){
                        if(prevConfig.config.allowedProducts
                            && prevConfig.config.allowedProducts.indexOf(options[i].products[j])>-1){
                            allowedProducts.push(options[i].products[j]);
                        }
                    }
                } else {
                    allowedProducts = options[i].products.clone();
                }

                if(allowedProducts.size()>0){
                    options[i].allowedProducts = allowedProducts;
                    element.options[index] = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);
                    element.options[index].config = options[i];
                    index++;
                }
            }
        }
        return;
        if (!element.nextSetting) {
            $('attribute-options-'+this.frontOptions.saPrefix+element.config.id).childElements().each(function (li, idx) {
                if (li.hintEl) li.hintEl.remove()
            })
        }
        $('attribute-options-'+this.frontOptions.saPrefix+element.config.id).update('')
        var html
        if (this.frontOptions.inProductList) {
            html = '<strong>'+this.frontOptions.titlePrefix+element.config.label+':</strong>'
            html += '<ol class="super-attribute-'+element.config.label.toLowerCase()+'">'
        } else {
            html = '<dt  id="dd-sao-'+[this.frontOptions.productId, element.config.id].join('-')+'" class="super-attribute-'+element.config.label.toLowerCase()+'"><span class="count">'+element.config.idx+'</span> <strong>Select '+this.frontOptions.titlePrefix+element.config.label+':</strong> <span class="finish-name"></span></dt>'
        }
        for (var i=1; i<element.options.length; i++) {
            if (this.frontOptions.inProductList) {
                html += '<li>'/*+'<small class="finish-name super-attribute-option-'+element.options[i].config.label.toLowerCase()+'">'+element.options[i].config.label+'</small>'*/+'<a href="javascript:void(0)"><img src="'+element.options[i].config.swatch+'" alt="'+element.options[i].config.label+'" /></a></li>'
            } else {
                html += '<dd id="dd-sao-'+[this.frontOptions.productId, element.config.id, element.options[i].config.id].join('-')+'"><span class="finish-name super-attribute-option-'+element.options[i].config.label.toLowerCase()+'">'+''/*element.options[i].config.label*/+'</span><a href="javascript:void(0)"><img src="'+element.options[i].config.swatch+'" alt="'+element.options[i].config.label+'"/><em></em></a></dd>'
            }
        }
        if (this.frontOptions.inProductList) {
            html += '</ol><span class="option-title"></span>'
        }
        $('attribute-options-'+this.frontOptions.saPrefix+element.config.id).update(html)
        $('attribute-options-'+this.frontOptions.saPrefix+element.config.id).select(this.frontOptions.inProductList ? 'li' : 'dd').each(function (li, idx) {
            li.down('a').observe('click', this.setStateConfigure.bindAsEventListener(this, element, element.options[idx+1].value))
            li.down('img').observe('mouseover', this.moUpdateOptionTitle.bindAsEventListener(this, element, li.down('img').alt))
        }.bind(this))
        var saDetails = $('attribute-options-'+this.frontOptions.saPrefix+element.config.id).up().down('.super-attribute-details')
        var saTargetId = 'super-attribute-details-'+this.config.productId+'_'+element.config.id
        if (saDetails && $(saTargetId)) {
            saDetails.observe('click', this.showSADetails.bindAsEventListener(this, saTargetId))
        }
    },
    moUpdateOptionTitle: function(e, element, optTitle) {
        var titleEl = $('attribute-options-'+this.frontOptions.saPrefix+element.config.id).down('span.option-title')
        if (titleEl) titleEl.update(optTitle)
    },
    markSelectedOption: function(element) {
        return;
        var swatchTag = this.frontOptions.inProductList ? 'li' : 'dd'
        $('attribute-options-'+this.frontOptions.saPrefix+element.config.id).select(swatchTag).each(function (li, idx) {
            if (idx+1 == element.selectedIndex) {
                var ddSao = $('dd-sao-'+[this.frontOptions.productId, element.config.id].join('-'))
                var ddSaoRef = $('dd-sao-ref-'+[this.frontOptions.productId, element.config.id].join('-'))
                if (ddSao && ddSao.select('.finish-name').length>0) {
                    ddSao.select('.finish-name')[0].update(element.options[idx+1].config.label)
                }
                if (ddSaoRef) {
                    if (ddSaoRef.select('.finish-name').length>0) {
                        ddSaoRef.select('.finish-name')[0].update(element.options[idx+1].config.label)
                    }
                    if (ddSaoRef.select('img').length>0) {
                        ddSaoRef.select('img')[0].src = element.options[idx+1].config.swatch
                    }
                }
                li.addClassName('selected')
                li.addClassName('active')
            } else {
                li.removeClassName('selected')
                li.removeClassName('active')
            }
        }.bind(this))
    },
    setStateConfigure2: function(e, element) {
        this.state[element.config.id] = element.options[element.selectedIndex].value;
        this.configureElement(element);
    },
    setStateConfigure: function(e, element, value) {
        this.state[element.config.id] = value
        this.configureElement(element)
    },
    setStateConfigureById: function(e, id, value) {
        var element
        for(var i=0; i<this.settings.length; i++) {
            if (this.settings[i].config.id==id) {
                element = this.settings[i];
                break;
            }
        }
        if (element) {
            this.state[element.config.id] = value
            this.configureElement(element)
        }
    },
    resetChildren : function($super, element) {
        var savedState
        savedState = this.state[element.config.id]
        $super(element)
        this.state[element.config.id] = savedState
    },
    getMatchingSimpleProduct: function(){
        var inScopeProductIds = this.getInScopeProductIds();
        if ((typeof inScopeProductIds != 'undefined') && (inScopeProductIds.length == 1)) {
            return inScopeProductIds[0];
        }
        return false;
    },
    getInScopeProductIds: function(optionalAllowedProducts) {

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
    }
});
