Varien.searchForm.addMethods({
	
    initAutocomplete: function (url, destinationElement) {
        var newUrl = url.replace('catalogsearch', 'solr');
        
        new Ajax.Autocompleter(
            this.field,            
            destinationElement,
            newUrl,
            {	
            	callback: function(element, entry) {
                    return entry + '&cat=' + $('cat').value;
                },
                paramName: this.field.name,
            	//parameters: Form.serialize(this.form),
            	//parameters: Form.serializeElements( $('#search_mini_form').getInputs('text') ),
            	//parameters: $('cat').serialize(false),
                //parameters: parseInt(document.forms[0].cat.options[document.forms[0].cat.selectedIndex].value).serialize(false),
            	//parameters: $('cat').selectedvalue,
            	//parameters: Form.serialize('cat:sdklf'),
                //parameters: $('catid').value,
                method: 'get',
                minChars: 2,
                updateElement: this._selectAutocompleteItem.bind(this),
                onShow: function (element, update) {
                    if (!update.style.position || update.style.position == 'absolute') {
                        update.style.position = 'absolute';
                        Position.clone(element, update, {
                            setHeight: false,
                            offsetTop: element.offsetHeight
                        });
                    }
                    Effect.Appear(update, {duration: 0});
                }

            }
        );
    },
    
    getCategoryId: function (){

    	Event.observe('cat', 'change', function(){
    		  if (this.selectedIndex >= 0) 
    			  $('catid').value = this.selectedIndex;
    		    $$('#cat option[selected]').invoke('writeAttribute', 'selected', false);
    		  return this.selectedIndex;
    	});    	 
    }	
});

/*
Element.addMethods("SELECT", {
    getSelectedOptionHTML: function(element) {
        if (!(element = $(element))) return;
        var index = element.selectedIndex;
        return index >= 0 ? element.options[index].innerHTML : undefined;
    }
);
*/

Event.observe('cat', 'change', function(){alert('d');
	  if (this.selectedIndex >= 0) 
	    $$('#cat option[selected]').invoke('writeAttribute', 'selected', false);
	});