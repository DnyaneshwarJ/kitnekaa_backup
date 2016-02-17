/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */



    FormElementDependenceController.prototype.trackChange  = function(e, idTo, valuesFrom)
    {
        // define whether the target should show up
        var shouldShowUp = true;
        for (var idFrom in valuesFrom) {
            var from = $(idFrom);
            if (valuesFrom[idFrom] instanceof Array) {
                if (!from || valuesFrom[idFrom].indexOf(from.value) == -1) {
                    shouldShowUp = false;
                }
            } else {
                if (!from || from.value != valuesFrom[idFrom]) {
                    shouldShowUp = false;
                }
            }
        }
        // toggle target row
        if($(idTo) != null)
        {
            if (shouldShowUp) {
                var currentConfig = this._config;
                $(idTo).up(this._config.levels_up).select('input', 'select', 'td').each(function (item) {
                    // don't touch hidden inputs (and Use Default inputs too), bc they may have custom logic
                    if ((!item.type || item.type != 'hidden') && !($(item.id+'_inherit') && $(item.id+'_inherit').checked)
                        && !(currentConfig.can_edit_price != undefined && !currentConfig.can_edit_price)) {
                        item.disabled = false;
                    }
                });
                $(idTo).up(this._config.levels_up).show();
            } else {
                $(idTo).up(this._config.levels_up).select('input', 'select', 'td').each(function (item){
                    // don't touch hidden inputs (and Use Default inputs too), bc they may have custom logic
                    if ((!item.type || item.type != 'hidden') && !($(item.id+'_inherit') && $(item.id+'_inherit').checked)) {
                        item.disabled = true;
                    }
                });
                $(idTo).up(this._config.levels_up).hide();
            }
        }
    }

