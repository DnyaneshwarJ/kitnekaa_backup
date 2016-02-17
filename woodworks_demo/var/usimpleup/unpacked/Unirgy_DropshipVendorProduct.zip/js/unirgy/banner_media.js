var UnirgyBannerGallery = Class.create({
    initialize: function(images, options) {
        this.options = {
            imageSwitchDuration: .5,
            containerId: 'banner-slider', 
            width: 950,
            height: 316,
            html_tpl: '<a href="#{target_url}"><img src="#{img_url} title="#{title}" width="#{width}" height="#{height}"/></a>',
            startPosition: 1,
            cycle: 'none',
            cycleDuration: 10
        }
        Object.extend(this.options, options)
        this.images = images
        this.selImgIdx = 0
        this.switching = false
        this.initImages()
        try {
            for (var i=0; i<this.images.length; i++) {
                var imgCfg = {}
                Object.extend(imgCfg, this.options)
                Object.extend(imgCfg, this.images[i])
                this.images[i] = imgCfg
                this.images[i].html_tpl = new Template(this.images[i].html_tpl)
                if (this.images[i].img_url) {
                    (new Image( )).src = this.images[i].img_url
                }
                $(this.options.containerId).select('.slider-nav')[0].insert('<li>&bull;</li>')
                try {
                    $(this.options.containerId).select('.slider-nav')[0].childElements()[i].observe('click', this.onSwitchSelImg.bindAsEventListener(this, i))
                } catch (e) {}
            }
        } catch (e) {}
        try {
            $(this.options.containerId).select('.arrow-left')[0].observe('click', this.onPrevImage.bindAsEventListener(this))
        } catch (e) {}
        try {
            $(this.options.containerId).select('.arrow-right')[0].observe('click', this.onNextImage.bindAsEventListener(this))
        } catch (e) {}
        if (this.images.length>0) {
            if (this.options.startPosition>1 && this.options.startPosition<=this.images.length) this.switchSelImg(this.options.startPosition-1)
            else this.switchSelImg(0)
        }

    },
    startCycle: function() {
        this.stopCycle()
        if (-1 != ['left','right'].indexOf(this.options.cycle) && this.options.cycleDuration>0) {
            if (this.options.cycle == 'left') {
                this.cycleLoop = new PeriodicalExecuter(this.prevImage.bind(this), this.options.cycleDuration)
            } else if (this.options.cycle == 'right') {
                this.cycleLoop = new PeriodicalExecuter(this.nextImage.bind(this), this.options.cycleDuration)
            }
        }
    },
    stopCycle: function() {
        try {
            this.cycleLoop.stop()
        } catch (e) {}
    },
    initImages: function() {
        this.frontImg = $(this.options.containerId).select('.content')[0]
        this.backImg = $(this.options.containerId).select('.content')[1]
        this.backImg.absolutize().clonePosition(this.frontImg).setOpacity(0)
        this.frontImg.setStyle({width: this.options.width+'px', height: this.options.height+'px'})
        this.backImg.setStyle({width: this.options.width+'px', height: this.options.height+'px'})
    },
    onPrevImage: function(e) {
        this.stopCycle()
        this.prevImage()
    },
    prevImage: function() {
        if (this.selImgIdx==0) this.switchSelImgIdx(this.images.length-1)
        else this.switchSelImgIdx(this.selImgIdx-1)
        this.switchImage()
    },
    onNextImage: function(e) {
        this.stopCycle()
        this.nextImage()
    },
    nextImage: function(e) {
        if (this.selImgIdx==this.images.length-1) this.switchSelImgIdx(0)
        else this.switchSelImgIdx(this.selImgIdx+1)
        this.switchImage()
    },
    onSwitchSelImg: function(e, newIdx) {
        this.switchSelImg(newIdx)
    },
    switchSelImg: function(newIdx) {
        this.switchSelImgIdx(newIdx)
        this.switchImage()
    },
    switchSelImgIdx: function(newIdx) {
        try {
            $(this.options.containerId).select('.slider-nav')[0].childElements()[this.selImgIdx].removeClassName('active')
        } catch (e) {}
        this.selImgIdx = newIdx
        try {
            $(this.options.containerId).select('.slider-nav')[0].childElements()[this.selImgIdx].addClassName('active')
        } catch (e) {}
        try {
            if (this.selImgIdx==0) $(this.options.containerId).select('.arrow-left')[0].addClassName('inactive')
            else $(this.options.containerId).select('.arrow-left')[0].removeClassName('inactive')
        } catch (e) {}
        try {
            if (this.selImgIdx==this.images.length-1) $(this.options.containerId).select('.arrow-right')[0].addClassName('inactive')
            else $(this.options.containerId).select('.arrow-right')[0].removeClassName('inactive')
        } catch (e) {}
    },
    switchImage: function() {
        //if (this.images[this.selImgIdx].url==this.frontImg.src) return
        this.stopEffects()
        this.setSwitching(false)
        var selImg = this.images[this.selImgIdx]
        this.backImg.update(selImg.html_tpl.evaluate(selImg))
        this.switchEffect = new Effect.Parallel([
            new Effect.Opacity(this.frontImg, { sync: true, from: 1, to: 0}),
            new Effect.Opacity(this.backImg, { sync: true, from: 0, to: 1})
        ], {duration: this.options.imageSwitchDuration, afterFinish: this.afterSwitch.bind(this)})
        this.backImg.show()
    },
    afterSwitch: function() {
        this.finishSwitch()
        this.setSwitching(false)
    },
    finishSwitch: function() {
        var oldFrontImg = this.frontImg
        this.frontImg = this.backImg
        this.backImg = oldFrontImg
        this.backImg.hide()
        this.startCycle()
    },
    setSwitching: function(flag) {
        this.switching = flag
    },
    stopEffects: function() {
        try {
            this.switchEffect.cancel()
        } catch(e) {}
        if (this.switching) {
            this.finishSwitch()
            this.frontImg.setOpacity(1)
            this.backImg.hide()
        }
    }
});
