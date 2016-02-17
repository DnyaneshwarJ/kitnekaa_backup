var UnirgyProductGallery = Class.create({
    initialize: function(images, options) {
        this.options = {
            imageSwitchDuration: .5,
            containerId: 'product-image', 
            activeThumbClass: 'active',
            isProductPage: false,
            width: 400,
            height: 445,
            softIdentify: false
        }
        Object.extend(this.options, options)
        this.images = images
        this.selSA = {}
        this.selImgIdx = null
        this.saKey = ''
        this.switching = false
        this.lastLoadIdx = -1
        this.initImages()
        if (this.options.isProductPage) this.initZoom()
        //if (this.options.isProductPage) this.preloadImages()
    },
    preloadImages: function(){
        for (var i=0; i<this.images.length; i++) {
            (new Image( )).src = this.images[i].url
        }
    },
    initZoom: function() {
        this.curSizeFlag = true
        this.isShowFlag = true
        this.zoomWrapper = $(this.options.zoomWrapperId)
        this.zoomWrapperPos = this.zoomWrapper.cumulativeOffset();
        this.zoomWrapper.observe('mouseover', this.zoomShow.bindAsEventListener(this))
        this.zoomWrapper.observe('mouseout', this.zoomHide.bindAsEventListener(this))
        this.zoomWrapper.observe('mousemove', this.zoomMove.bindAsEventListener(this))
        this.zoomWrapper.observe('click', this.zoomClick.bindAsEventListener(this))
        this.zoomSmallSize = this.zoomWrapper.getDimensions()
        this.zoomLens = $(this.options.zoomLensId);
        this.zoomView = $(this.options.zoomLargeViewId)
        this.zoomViewSize = this.zoomView.getDimensions()
        this.zoomLargeImg = $(this.options.zoomLargeImgId)
        this.zoomImgLoader = $(this.options.zoomImgLoaderId)
        this.zoomWrapper.setStyle({position:'relative'});
        this.zoomLens.setStyle({display:'none', position:'absolute', cursor:'move'});
        this.zoomView.setStyle({display:'none', position:'relative', overflow:'hidden'});
        this.zoomLargeImg.setStyle({position:'absolute'});
    },
    initImages: function() {
        this.frontImg = $(this.options.containerId).down('img')
        this.backImg = $(document.createElement('img'))
        $(this.options.containerId).setStyle({position: 'relative'})
        $(this.options.containerId).insert(this.backImg)
        this.backImg.absolutize().clonePosition(this.frontImg).setOpacity(0)
        this.frontImg.absolutize()
        this.frontImg.setStyle({width: this.options.width+'px', height: this.options.height+'px'})
        this.backImg.setStyle({width: this.options.width+'px', height: this.options.height+'px'})
        this.selImgIdx = 0;
        for (var i=0; i<this.images.length; i++) {
            if (this.images[i].url == this.frontImg.src) {
                this.selImgIdx = i
                break
            }
        }
    },
    superAttributesInit: function(spConfig)
    {
        try {
            this._superAttributesChanged(spConfig,true)
        } catch(e) {}
        if (this.selImgIdx == null && this.lastLoadIdx<0) {
            this.switchImage(0,true)
        }
    },
    superAttributesChanged: function(spConfig)
    {
        try {
            this._superAttributesChanged(spConfig,false)
        } catch(e) {}
    },
    _superAttributesChanged: function(spConfig, noEffect)
    {
        var saKeyArr = []
        for (var i=0; i<spConfig.settings.length; i++) {
            aSel = spConfig.settings[i]
            if (aSel.config.identifyImage>0) {
                if (Object.isArray(this.options.softIdentify) 
                    && this.options.softIdentify.length>0
                    && -1 != $A(this.options.softIdentify).indexOf(aSel.config.id)
                ) {
                    this.selSA[aSel.config.id] = aSel.value
                    saKeyArr.push(aSel.config.id+'='+aSel.value)
                } else if (!this.options.softIdentify
                    || !Object.isArray(this.options.softIdentify) 
                    || this.options.softIdentify.length == 0
                ) {
                    this.selSA[aSel.config.id] = aSel.value
                    saKeyArr.push(aSel.config.id+'='+aSel.value)
                }
            }
        }
        if (this.saKey == saKeyArr.join(';')) return
        this.saKey = saKeyArr.join(';')
        for (var i=0; i<this.images.length; i++) {
            var imgSaKey
            if (Object.isArray(this.options.softIdentify) 
                && this.options.softIdentify.length>0
            ) {
                var imgSaId, imgSaKeyArr = []
                for (imgSaId in this.images[i]['superAttribute']) {
                    if (-1 != $A(this.options.softIdentify).indexOf(imgSaId)) {
                        imgSaKeyArr.push(imgSaId+'='+this.images[i]['superAttribute'][imgSaId])
                    }
                }
                imgSaKey = imgSaKeyArr.join(';')
            } else {
                imgSaKey = this.images[i].superAttributeKey
            }
            if (imgSaKey == this.saKey && this.images[i].superAttribute.main) {
                this.switchImage(i, noEffect)
                break
            }
        }
    },
    onPrevImage: function(e) {
        if (this.selImgIdx==0) return
        if (this.switching) return 
        this.switchImage(this.selImgIdx-1)
    },
    onNextImage: function(e) {
        if (this.selImgIdx==this.images.length-1) return
        if (this.switching) return 
        this.switchImage(this.selImgIdx+1)
    },
    switchSelImgIdx: function(newIdx) {
        try {
            $('gi-thumb-'+this.images[this.selImgIdx].id).removeClassName(this.options.activeThumbClass)
        } catch (e) {}
        this.selImgIdx = newIdx
        try {
            $('gi-thumb-'+this.images[this.selImgIdx].id).addClassName(this.options.activeThumbClass)
        } catch (e) {}
    },
    selectImageById: function(imgId) {
        if (this.switching) return 
        for (var i=0; i<this.images.length; i++) {
            if (this.images[i].id == imgId) {
                this.switchImage(i,false)
                break
            }
        }
    },
    switchImage: function(newIdx, noEffect) {
        this.lastLoadIdx=newIdx
        if (this.images[newIdx].isImgLoaded) {
            this._switchImage(newIdx, noEffect)
        } else {
            var imgToLoad = new Image()
            Event.observe(imgToLoad, 'load', this._switchImage.bind(this, newIdx, noEffect))
            imgToLoad.src = this.images[newIdx].url
        }
    },
    _switchImage: function(newIdx, noEffect) {
        this.images[newIdx].isImgLoaded = true
        if (this.lastLoadIdx!=newIdx) return;
        if (1||noEffect) {
            this.switchSelImgIdx(newIdx)
            this.backImg.src = this.images[this.selImgIdx].url
            if (this.zoomLargeImg) this.zoomLargeImg.src = this.images[this.selImgIdx].mid_url
            this.finishSwitch()
            return 
        } else {
            this.switchSelImgIdx(newIdx)
        }
        if (this.images[this.selImgIdx].url==this.frontImg.src) return
        this.stopEffects()
        this.setSwitching(false)
        this.backImg.src = ''
        this.frontImg.show()
        this.doSwitch.bind(this).defer()
    },
    doSwitch: function() {
        this.backImg.src = this.images[this.selImgIdx].url
        if (this.zoomLargeImg) this.zoomLargeImg.src = this.images[this.selImgIdx].mid_url
        this.switchEffect = new Effect.Parallel([
            new Effect.Opacity(this.frontImg, { sync: true, from: 1, to: 0}),
            new Effect.Opacity(this.backImg, { sync: true, from: 0, to: 1})
        ], {duration: this.options.imageSwitchDuration, afterFinish: this.afterSwitch.bind(this)})
    },
    afterSwitch: function() {
        this.finishSwitch()
        this.setSwitching(false)
    },
    finishSwitch: function() {
        var oldFrontImg = this.frontImg
        this.frontImg = this.backImg
        this.backImg = oldFrontImg
        this.frontImg.setOpacity(1).show()
        this.backImg.setOpacity(0)
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
        }
    },
    zoomCurSize: function() {
        return this.curSizeFlag ? this.images[this.selImgIdx].mid_size : this.images[this.selImgIdx].full_size
    },
    zoomLensSize: function () {
        return {
            width: this.zoomViewSize.width*this.zoomSmallSize.width/(this.zoomCurSize()[0]),
            height: this.zoomViewSize.height*this.zoomSmallSize.height/(this.zoomCurSize()[1])
        }
    },
    zoomLoadImage: function(url, reqIdx, reqSizeFlag) {
        var imgToLoad = new Image()
        Event.observe(imgToLoad, 'load', function(ev, reqIdx, reqSizeFlag) {
            if (this.isShowFlag && reqIdx == this.selImgIdx && reqSizeFlag == this.curSizeFlag) {
                if (this.curSizeFlag) {
                    this.images[this.selImgIdx].mid_loaded = true
                } else {
                    this.images[this.selImgIdx].full_loaded = true
                }
                this.zoomShow()
            }
        }.bindAsEventListener(this, reqIdx, reqSizeFlag))
        imgToLoad.src = url
    },
    showLoading: function(flag) {
        try {
            if (flag) {
                this.zoomWrapper.addClassName('loading')
                this.zoomImgLoader.show()
            } else {
                this.zoomWrapper.removeClassName('loading')
                this.zoomImgLoader.hide()
            }
        } catch (e) {}
    },
    zoomShow: function(ev) {
        if (!this.checkZoom()) return;
        this.isShowFlag = true
        if (this.curSizeFlag && !this.images[this.selImgIdx].mid_loaded) {
            this.zoomHide()
            this.showLoading(true)
            this.isShowFlag = true
            this.zoomLoadImage(this.images[this.selImgIdx].mid_url, this.selImgIdx, this.curSizeFlag)
            return
        } else if (!this.curSizeFlag && !this.images[this.selImgIdx].full_loaded) {
            this.zoomHide()
            this.showLoading(true)
            this.isShowFlag = true
            this.zoomLoadImage(this.images[this.selImgIdx].full_url, this.selImgIdx, this.curSizeFlag)
            return
        }
        this.showLoading(false)
        this.zoomLargeImg.src = this.curSizeFlag ? this.images[this.selImgIdx].mid_url : this.images[this.selImgIdx].full_url
        this.zoomLens.setStyle({width:this.zoomLensSize().width+'px', height:this.zoomLensSize().height+'px', opacity: 0.5})
        if (this.curSizeFlag) {
            this.zoomLens.update(this.options.zoomInText)
        } else {
            this.zoomLens.update(this.options.zoomOutText)
        }
        this.zoomLens.show()
        this.zoomView.show()
    },
    zoomHide: function(ev) {
        this.isShowFlag = false
        this.showLoading(false)
        this.zoomLens.hide()
        this.zoomView.hide()
    },
    zoomMove: function(ev) {
        if (!this.checkZoom()) return;
        this.isShowFlag = true
        var lensLeft = Math.min(Math.max(ev.pageX-this.zoomWrapperPos.left-this.zoomLensSize().width/2, 0), this.zoomSmallSize.width-this.zoomLensSize().width)
        var lensTop = Math.min(Math.max(ev.pageY-this.zoomWrapperPos.top-this.zoomLensSize().height/2, 0), this.zoomSmallSize.height-this.zoomLensSize().height)
        this.zoomLens.setStyle({left:lensLeft+'px', top:lensTop+'px'})
        var largeLeft = -lensLeft/this.zoomSmallSize.width*(this.zoomCurSize()[0])
        var largeTop = -lensTop/this.zoomSmallSize.height*(this.zoomCurSize()[1])
        this.zoomLargeImg.setStyle({left:largeLeft+'px', top:largeTop+'px'})
    },
    zoomClick: function(ev) {
        if (!this.checkZoom()) return;
        this.isShowFlag = true
        this.curSizeFlag = !this.curSizeFlag
        this.zoomShow(ev)
        this.zoomMove(ev)
    },
    checkZoom: function() {
        if (!this.isZoomPossible()) {
            this.zoomHide();
            return false;
        }
        return true;
    },
    isZoomPossible: function () {
        return this.zoomCurSize()[0]>this.zoomViewSize.width
            && this.zoomCurSize()[1]>this.zoomViewSize.height;
    }

});

