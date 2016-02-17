var UnirgyModal = Class.create({
    initialize: function() {
        this.curModal = this.skipNextClick = this.stickyModal = false
        Event.observe(document.body, 'click', this.onSomeWhereClick.bindAsEventListener(this))
        Event.observe(window, 'keyup', this.processEsc.bindAsEventListener(this))
        this.hideCurrentModalBind = this.hideCurrentModal.bind(this)
    },
    processEsc: function(e) {
        if(e.keyCode==Event.KEY_ESC){
            this.hideCurrentModal();
        }
    },
    onSomeWhereClick: function(e) {
        if (!this.stickyModal && !this.skipNextClick && this.curModal && this.curModal.down() && !this.within(this.curModal.down(), Event.pointerX(e), Event.pointerY(e))) {
            this.hideCurrentModal(true);
        } else {
            this.skipNextClick = false
        }
    },
    within: function(el, x, y) {
        var co = el.cumulativeOffset()
        var dim = el.getDimensions()
        if (x<co[0] || y<co[1]) return false
        if (x>co[0]+dim.width || y>co[1]+dim.height) return false
        return true
    },

    switchCurrentModal: function(newModal, skipNextClick) {
        if (this.curModal) this.hideCurrentModal(true);
        this.curModal = newModal
        this.skipNextClick = skipNextClick
    },
    showModal: function(newModal, skipNextClick, mode) {
        if (this.curModal) this.hideCurrentModal(true);
        this.curModal = newModal
        this.skipNextClick = skipNextClick
        var modalDim = {
            width: (document.body.scrollWidth-1)+'px',
            height: (document.body.scrollHeight-1)+'px'
        }
        var myWidth, myHeight;
        if( typeof( window.innerWidth ) == 'number' ) {
        //Non-IE
            myWidth = window.innerWidth;
            myHeight = window.innerHeight;
        } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
        //IE 6+ in 'standards compliant mode'
            myWidth = document.documentElement.clientWidth;
            myHeight = document.documentElement.clientHeight;
        } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
        //IE 4 compatible
            myWidth = document.body.clientWidth;
            myHeight = document.body.clientHeight;
        }

        /*
        var modalDim = {
            width: (document.body.clientWidth-1)+'px',
            height: (document.body.clientHeight-1)+'px'
        }
        */
        var modalStyle = Object.extend({
            top: '0px', left: '0px',
            position: 'absolute',
            opacity: 0
        }, modalDim)
        this.prevParent = this.curModal.parentNode;
        document.body.appendChild(this.curModal)
        this.curModal.setStyle(modalStyle)
        this.curModal.show()
        /*
        if ((/msie 7\./i).test(navigator.userAgent)) {
            this.curModal.select('.modal-main')[0].setStyle({
                height: this.curModal.select('.modal-main .content')[0].getHeight()+'px'
            })
        }
        */
        /*
        var modalHeight = Math.max(
            this.curModal.select('.modal')[0].getHeight(),
            this.curModal.select('.modal-main')[0].getHeight(),
            this.curModal.select('.modal-main .content')[0].getHeight()
        ).round()
        */
        var modalHeight = this.curModal.select('.modal')[0].getHeight();
        this.curModal.down().absolutize()
        /*this.curModal.down().setStyle({
            zIndex: 10000,
            left: (
                document.viewport.getScrollOffsets().left
                +(document.viewport.getWidth()-this.curModal.select('.modal-main')[0].getWidth())/2).round()+'px',
            top: (
                document.viewport.getScrollOffsets().top
                +(document.viewport.getHeight()-modalHeight)/2).round()+'px'
        })*/
        var showStyle = {
            zIndex: 10000,
            left: (
                document.viewport.getScrollOffsets().left
                +(myWidth-this.curModal.select('.modal-main')[0].getWidth())/2).round(),
            top: (
                document.viewport.getScrollOffsets().top
                +(myHeight-modalHeight)/2).round()
        };
        if (Object.isArray(mode) && mode.length>=2 && mode[0] && $(mode[1])) {
            if (mode[0]&this.center) {
                showStyle.left = $(mode[1]).cumulativeOffset().left
                    +(($(mode[1]).getWidth()-this.curModal.select('.modal')[0].getWidth())/2).round();
            }
            if (mode[0]&this.left) {
                showStyle.left = $(mode[1]).cumulativeOffset().left
                    -this.curModal.select('.modal')[0].getWidth();
            }
            if (mode[0]&this.right) {
                showStyle.left = $(mode[1]).cumulativeOffset().left
                    +$(mode[1]).getWidth();
            }
            if (mode[0]&this.middle) {
                showStyle.top = $(mode[1]).cumulativeOffset().top
                    +(($(mode[1]).getHeight()-this.curModal.select('.modal')[0].getHeight())/2).round();
            }
            if (mode[0]&this.top) {
                showStyle.top = $(mode[1]).cumulativeOffset().top
                    -this.curModal.select('.modal')[0].getHeight();
            }
            if (mode[0]&this.bottom) {
                showStyle.top = $(mode[1]).cumulativeOffset().top
                    +$(mode[1]).getHeight();
            }
            if (Object.isNumber(mode[2])) {
                showStyle.left+=mode[2];
            }
            if (Object.isNumber(mode[3])) {
                showStyle.top+=mode[3];
            }
        }
        showStyle.left+='px';
        showStyle.top+='px';
        this.curModal.down().setStyle(showStyle);
        this.curModal.appear({ duration:.2 });
    },
    hideCurrentModal: function(noEffect) {
        if (this.curModal) {
            if (noEffect) {
                this.curModal.hide();
                this.backModalPosition();
            } else {
                this.curModal.fade({ duration:1.0, afterFinish: this.backModalPosition.bind(this)});
            }
        }
        this.curModal = false;
        this.prevParent = false;
        this.stickyModal = false
    },
    backModalPosition: function() {
        if (this.prevParent) {
            this.prevParent.appendChild(this.curModal);
        }
    },
    center: 1,
    left: 2,
    right: 4,
    top: 8,
    middle: 16,
    bottom: 32,
    centerTop: function(){
        return this.center|this.top;
    },
    centerBottom: function(){
        return this.center|this.bottom;
    },
    centerMiddle: function(){
        return this.center|this.middle;
    },
    leftTop: function(){
        return this.left|this.top;
    },
    leftBottom: function(){
        return this.left|this.bottom;
    },
    leftMiddle: function(){
        return this.left|this.middle;
    },
    rightTop: function(){
        return this.right|this.top;
    },
    rightBottom: function(){
        return this.right|this.bottom;
    },
    rightMiddle: function(){
        return this.right|this.middle;
    }
});

var UnirgyModalSingleton

function unirgyGetModal() {
    if (!UnirgyModalSingleton) {
        UnirgyModalSingleton = new UnirgyModal()
    }
    return UnirgyModalSingleton
}

function unirgyShowThisModal(modalId, mode) {
    if ($(modalId)) {
        unirgyGetModal().showModal($(modalId), true, mode)
    }
}

function unirgyShowThisSizeChartModal(scmId, mode) {
    if ($(scmId)) {
        unirgyGetModal().showModal($(scmId), true, mode)
    }
}

function unirgyShowSizeChartModal(mode) {
    if ($('size-chart-modal')) {
        unirgyGetModal().showModal($('size-chart-modal'), true, mode)
    }
}


