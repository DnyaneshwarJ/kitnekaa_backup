var UnirgyCountdown = Class.create({
    initialize: function(options) {
        this.dayValue = 24*60*60*1000
        this.hourValue = 60*60*1000
        this.minuteValue = 60*1000
        this.secondValue = 1000
        if (!options.containers) return;
    	this.dayLabel = options.dayLabel || 'Days'
        this.hourLabel = options.hourLabel ||  'Hrs'
        this.minuteLabel = options.minuteLabel ||  'Min'
        this.secondLabel = options.secondLabel ||  'sec'
        var container, targetTime, tDate;
        this.containers = [];
        for (i=0; i<options.containers.length; i++) {
            if ((container = $(options.containers[i].id)) && (targetTime = Date.parse(options.containers[i].targetDate))) {
                tDate = new Date()
                targetTime = targetTime-tDate.getTimezoneOffset()*60*1000
                tDate.setTime(targetTime)
                this.containers.push({containerId: options.containers[i].id, expiredId: options.containers[i].expiredId, targetDate: tDate})
            }
        }
        for (i=0; i<this.containers.length; i++) {
            this.containers[i].loopExec = new PeriodicalExecuter(this.updateCountdown.bind(this,this.containers[i]), 1)
        }
    },
    getTimeLeft: function(cId) {
        for (i=0; i<this.containers.length; i++) {
            if (this.containers[i].containerId==cId) {
                return this.containers[i].targetDate.getTime()-(new Date()).getTime()
            }
        }
        return 0
    },
    updateCountdown: function(container) {
        var timeLeft = container.targetDate.getTime()-(new Date()).getTime()
        var hourLeft, minuteLeft, secondLeft
        if (timeLeft<=0) {
            dayLeft = hourLeft = minuteLeft = secondLeft = new String(0)
        } else {
            dayLeft    = Math.floor(timeLeft/this.dayValue)
            if (dayLeft>=4) timeLeft  -= dayLeft*this.dayValue
            else dayLeft = 0
            hourLeft   = Math.floor(timeLeft/this.hourValue)
            timeLeft  -= hourLeft*this.hourValue
            minuteLeft = Math.floor(timeLeft/this.minuteValue)
            timeLeft  -= minuteLeft*this.minuteValue
            secondLeft = Math.floor(timeLeft/this.secondValue)
        }
        var dayContainer, dayLabelContainer
        var hourContainer = $$('#'+container.containerId+' .hour')
        var minuteContainer = $$('#'+container.containerId+' .minute')
        var secondContainer = $$('#'+container.containerId+' .second')
        var hourLabelContainer = $$('#'+container.containerId+' .hour-label')
        var minuteLabelContainer = $$('#'+container.containerId+' .minute-label')
        var secondLabelContainer = $$('#'+container.containerId+' .second-label')
        if (dayLeft>0) {
            dayContainer    = hourContainer
            hourContainer   = minuteContainer
            minuteContainer = secondContainer
            secondContainer = null
            dayLabelContainer    = hourLabelContainer
            hourLabelContainer   = minuteLabelContainer
            minuteLabelContainer = secondLabelContainer
            secondLabelContainer = null
        }
        if (dayContainer && dayContainer[0]) {
            //dayContainer[0].update(dayLeft<10 ? '0'+dayLeft : dayLeft)
            dayContainer[0].update(dayLeft)
        }
        if (hourContainer && hourContainer[0]) {
            //hourContainer[0].update(hourLeft<10 ? '0'+hourLeft : hourLeft)
            hourContainer[0].update(hourLeft)
        }
        if (minuteContainer && minuteContainer[0]) {
            //minuteContainer[0].update(minuteLeft<10 ? '0'+minuteLeft : minuteLeft)
            minuteContainer[0].update(minuteLeft)
        }
        if (secondContainer && secondContainer[0]) {
            //secondContainer[0].update(secondLeft<10 ? '0'+secondLeft : secondLeft)
            secondContainer[0].update(secondLeft)
        }
        if (dayLabelContainer && dayLabelContainer[0]) {
            dayLabelContainer[0].update(this.dayLabel)
        }
        if (hourLabelContainer && hourLabelContainer[0]) {
            hourLabelContainer[0].update(this.hourLabel)
        }
        if (minuteLabelContainer && minuteLabelContainer[0]) {
            minuteLabelContainer[0].update(this.minuteLabel)
        }
        if (secondLabelContainer && secondLabelContainer[0]) {
            secondLabelContainer[0].update(this.secondLabel)
        }
        if (timeLeft<=0 && container.expiredId && $(container.expiredId)) {
            $(container.containerId).hide()
            $(container.expiredId).show()
        } else {
            $(container.containerId).show()
            if (container.expiredId && $(container.expiredId)) $(container.expiredId).hide()
        }
    }
});
