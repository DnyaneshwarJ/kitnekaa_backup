varienGrid = Class.create(varienGrid, {
    initialize : function($super, containerId, url, pageVar, sortVar, dirVar, filterVar) {
        $super(containerId, url, pageVar, sortVar, dirVar, filterVar)
        varienGlobalEvents.fireEvent('uGridInitAfter', this);
    }
})
