var UnirgySlider = Class.create({
    initialize: function(options) {
        this.options = {
            moveDuration: .5,
            moveStep: 1,
            prevClass: 'arrow-left',
            nextClass: 'arrow-right',
            startPosition: 0,
            cycle: 'none',
            cycleDuration: 10
        }
        Object.extend(this.options, options)
        if (!$(this.options.containerId)) return;
        if (this.options.container && $(this.options.container)) {
            this.container = $(this.options.container)
        } else {
            this.container = $(this.options.containerId)
        }
        if (this.options.itemContainer && $(this.options.itemContainer)) {
            this.itemContainer = $(this.options.itemContainer)
        } else if (this.options.itemContainerId && $(this.options.itemContainerId)) {
            this.itemContainer = $(this.options.itemContainerId)
        } else if (this.options.itemContainerClass
            && (this.itemContainer = this.container.select('.'+this.options.itemContainerClass))
            && this.itemContainer.size()>0
        ) {
            this.itemContainer = this.itemContainer[0]
        } else {
            this.itemContainer = this.container
        }
        if (this.options.itemClass) {
            this.items = this.itemContainer.childElements().findAll((function(el){
                return el.hasClassName(this.options.itemClass)
            }).bind(this));
        } else {
            this.items = this.itemContainer.childElements()
        }
        this.itemCnt = this.items.size()
        if (this.itemCnt==0) return
        if (this.options.itemsBox && this.options.itemBoxWidth && $(this.options.itemsBox)) {
            $(this.options.itemsBox).setStyle({width: (this.itemCnt*this.options.itemBoxWidth)+'px'});
        }
        if (this.options.navigationContainer && $(this.options.navigationContainer)) {
            this.navigationContainer = $(this.options.navigationContainer)
        }
        if (this.options.navigationItems && this.options.navigationItems.length>0) {
            this.navigationItems = this.options.navigationItems
        } else if (this.navigationContainer) {
            if (this.navigationContainer.childElements().size()>0) {
                this.navigationItems = this.navigationContainer.childElements()
            } else {
                this.navigationItems = []
                for (i=0; i<Math.ceil(this.itemCnt/this.options.moveStep); i++) {
                    if (this.options.navItemHtml) {
                        navItemHtml = this.options.navItemHtml;
                    } else if (-1 != ['ul','ol'].indexOf(this.itemContainer.tagName.toLowerCase())) {
                        navItemHtml = '<li><a href="javascript:void(0);">&bull;</a></li>';
                    } else {
                        navItemHtml = '<a href="javascript:void(0);">&bull;</a>';
                    }
                    this.navigationContainer.insert({bottom: navItemHtml})
                    this.navigationItems.push(this.navigationContainer.childElements().last())
                }
            }
        }
        if (this.navigationItems && this.navigationItems.length>0) {
            for (i=0; i<this.navigationItems.length; i++) {
                this.navigationItems[i].observe('click', this.onMoveByIdx.bindAsEventListener(this, i*this.options.moveStep))
            }
        }
        this.moveWidth = this.options.moveWidth || this.items[0].getDimensions().width
        this.options.itemsInContainer = Math.max(this.options.itemsInContainer || 0, this.options.moveStep)
        this.currentIdx = 0
        try {
            this.prevBtn = $(this.options.prevBtnId) || this.container.select('.'+this.options.prevClass)[0]
            this.prevBtn.observe('click', this.onMovePrev.bindAsEventListener(this))
        } catch (e) {}
        try {
            this.nextBtn = $(this.options.nextBtnId) || this.container.select('.'+this.options.nextClass)[0]
            this.nextBtn.observe('click', this.onMoveNext.bindAsEventListener(this))
        } catch (e) {}
        this.moveLock = false
        if (this.options.startPosition>1 && this.options.startPosition<=this.itemCnt) this.moveByIdx(this.options.startPosition-1, true)
        this.startCycle()
        this.processPrevNextState()
    },
    startCycle: function() {
        this.stopCycle()
        if (-1 != ['left','right'].indexOf(this.options.cycle) && this.options.cycleDuration>0) {
            if (this.options.cycle == 'left') {
                this.cycleLoop = new PeriodicalExecuter(this.movePrev.bind(this, false), this.options.cycleDuration)
            } else if (this.options.cycle == 'right') {
                this.cycleLoop = new PeriodicalExecuter(this.moveNext.bind(this, false), this.options.cycleDuration)
            }
        }
    },
    stopCycle: function() {
        try {
            this.cycleLoop.stop()
        } catch (e) {}
    },
    processPrevNextState: function() {
        try {
            if (this.currentIdx==0) this.prevBtn.addClassName('inactive')
            else this.prevBtn.removeClassName('inactive')
        } catch (e) {}
        try {
            if (this.currentIdx+this.options.moveStep>=this.itemCnt) this.nextBtn.addClassName('inactive')
            else this.nextBtn.removeClassName('inactive')
        } catch (e) {}
        try {
            this.navigationItems.invoke('removeClassName', 'active')
            this.navigationItems[Math.ceil(this.currentIdx/this.options.moveStep)].addClassName('active')
        } catch (e) {}
    },
    onMovePrev: function(e) {
        this.stopCycle()
        this.movePrev(false)
    },
    movePrev: function (instant) {
        if (this.moveLock) return
        if (this.currentIdx-this.options.moveStep>=0) {
            this.moveLock = true
            this.currentIdx -= this.options.moveStep
            if (instant) {
                new Effect.Move(this.itemContainer, {x:this.moveWidth*this.options.moveStep, duration: 0, afterFinish: this.clearMoveLock.bind(this)})
            } else {
                new Effect.Move(this.itemContainer, {x:this.moveWidth*this.options.moveStep, duration: this.options.moveDuration, afterFinish: this.clearMoveLock.bind(this)})
            }
            this.processPrevNextState()
        } else if (this.options.cycle=='left') {
            this.moveByIdx(this.itemCnt-1)
        }
    },
    onMoveNext: function(e) {
        this.stopCycle()
        this.moveNext(false)
    },
    moveNext: function (instant) {
        if (this.moveLock) return
        if (this.currentIdx+this.options.itemsInContainer<this.itemCnt) {
            this.moveLock = true
            this.currentIdx += this.options.moveStep
            if (instant) {
                new Effect.Move(this.itemContainer, {x:-1*this.moveWidth*this.options.moveStep, duration: 0, afterFinish: this.clearMoveLock.bind(this)})
            } else {
                new Effect.Move(this.itemContainer, {x:-1*this.moveWidth*this.options.moveStep, duration: this.options.moveDuration, afterFinish: this.clearMoveLock.bind(this)})
            }
            this.processPrevNextState()
        } else if (this.options.cycle=='right') {
            this.moveByIdx(0)
        }
    },
    onMoveByIdx: function (e, idx) {
        this.stopCycle()
        this.moveByIdx(idx, false)
    },
    moveByIdx: function (idx, instant) {
        if (this.moveLock || idx<0 || idx>=this.itemCnt || this.currentIdx == idx) return
        this.moveLock = true
        if (instant) {
            new Effect.Move(this.itemContainer, {x:-1*this.moveWidth*(idx-this.currentIdx), duration: 0, afterFinish: this.clearMoveLock.bind(this)})
        } else {
            new Effect.Move(this.itemContainer, {x:-1*this.moveWidth*(idx-this.currentIdx), duration: this.options.moveDuration, afterFinish: this.clearMoveLock.bind(this)})
        }
        this.currentIdx = idx
        this.processPrevNextState()
    },
    clearMoveLock: function() {
        this.moveLock = false
        this.startCycle()
    }
});
