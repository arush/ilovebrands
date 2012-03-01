Evogue.Slider = Class.create();

Object.extend(Evogue.Slider.prototype, {
    initialize: function (container, config) {
        this.container = $(container);
        this.config = {
            panelCss: 'slider-panel',
            sliderCss: 'slider',
            itemCss: 'slider-item',
            slideButtonCss: 'slide-button',
            slideButtonInactiveCss: 'inactive',
            forwardButtonCss: 'forward',
            backwardButtonCss: 'backward',
            pageSize: 6,
            scrollSize: 2,
            slideDuration: 1.0,
            slideDirection: 'horizontal',
            fadeEffect: true
        };

        Object.extend(this.config, config || {});

        this.items = this.container.select('.' + this.config.itemCss);
        this.isPlaying = false;
        this.isAbsolutized = false;
        this.offset = 0;
        this.onClick = this.handleClick.bindAsEventListener(this);
        this.sliderPanel = this.container.down('.' + this.config.panelCss);
        this.slider =  this.sliderPanel.down('.' + this.config.sliderCss);
        this.container.select('.' + this.config.slideButtonCss).each(
            this.initializeHandlers.bind(this)
        );
        this.updateButtons();
        
        Event.observe(window, 'load', this.initializeDimensions.bind(this));
    },
    initializeHandlers: function (element) {
        if (element.hasClassName(this.config.forwardButtonCss) ||
            element.hasClassName(this.config.backwardButtonCss)) {
            element.observe('click', this.onClick);
        }
    },
    handleClick: function (evt) {
        var element = Event.element(evt);
        if (!element.hasClassName(this.config.slideButtonCss)) {
            element = element.up('.' + this.config.slideButtonCss);
        }

        if (!element.hasClassName(this.config.slideButtonInactiveCss)) {
           element.hasClassName(this.config.forwardButtonCss) || this.backward();
           element.hasClassName(this.config.backwardButtonCss) || this.forward();
        }
        Event.stop(evt);
    },
    updateButtons: function () {
        var buttons = this.container.select('.' + this.config.slideButtonCss);
        for (var i = 0, l = buttons.length; i < l; i++) {
            if (buttons[i].hasClassName(this.config.backwardButtonCss)) {
                if (this.offset <= 0) {
                    buttons[i].addClassName(this.config.slideButtonInactiveCss);
                }
                else {
                    buttons[i].removeClassName(this.config.slideButtonInactiveCss);
                }
            } else if (buttons[i].hasClassName(this.config.forwardButtonCss)) {
                if (this.offset >= this.items.length - this.config.pageSize) {
                    buttons[i].addClassName(this.config.slideButtonInactiveCss);
                }
                else {
                    buttons[i].removeClassName(this.config.slideButtonInactiveCss);
                }
            }
        }
    },
    initializeDimensions: function () {
        if ((this.config.slideDirection == 'horizontal' && this.sliderPanel.style.width) ||
            (this.config.slideDirection != 'horizontal' && this.sliderPanel.style.height)) {
            return this;
        }
        var firstItem = this.items.first();
        var offset = 0;
        if (this.config.slideDirection == 'horizontal') {
            offset = (parseInt(firstItem.getStyle('margin-left')) + parseInt(firstItem.getStyle('margin-right'))) * (this.config.pageSize - 1);
            this.sliderPanel.setStyle({width: (firstItem.getDimensions().width * this.config.pageSize + offset) + 'px'});
        } else {
            offset = (parseInt(firstItem.getStyle('margin-bottom')) + parseInt(firstItem.getStyle('margin-top'))) * (this.config.pageSize - 1);
            this.sliderPanel.setStyle({height: (firstItem.getDimensions().height * this.config.pageSize + offset) + 'px'});
        }
        
        var dimensions = this.sliderPanel.getDimensions();
        
        var sliderParent = this.sliderPanel.up();
        /*
        dimensions.height += parseInt(sliderParent.getStyle('padding-top'));
        dimensions.height += parseInt(sliderParent.getStyle('padding-bottom'));
        dimensions.width += parseInt(sliderParent.getStyle('padding-left'));
        dimensions.width += parseInt(sliderParent.getStyle('padding-right'));
    
        if (sliderParent.down('.slide-button')) {
            var buttonDimensions = sliderParent.down('.slide-button').getDimensions();
            if (this.config.slideDirection == 'horizontal') {
                dimensions.width += 2 * buttonDimensions.width;
            } else {
                dimensions.height += 2 * buttonDimensions.height;
            }
        }
        */
        sliderParent.setStyle({
            width: dimensions.width + 'px',
            height: dimensions.height + 'px'
        });
        return this;
    },
    absolutize: function () {
        if (!this.isAbsolutized) {
            this.isAbsolutized = true;
            var dimensions = this.sliderPanel.getDimensions();
            this.sliderPanel.setStyle({
                height: dimensions.height + 'px',
                width: dimensions.width + 'px'
            });
            
            this.slider.absolutize();
        }
    },
    
    forward: function () {
        if (this.offset + this.config.pageSize <= this.items.length - 1) {
            this.slide(true);
        }
    },
    backward: function () {
        if (this.offset > 0) {
            this.slide(false);
        }
    },
    slide: function (isForward) {

        if (this.isPlaying) {
            return;
        }
        this.absolutize();
        this.effectConfig = {
            duration: this.config.slideDuration
        };
        if (this.config.slideDirection == 'horizontal') {
            this.effectConfig.x = this.getSlidePosition(isForward).left;
        } else {
            this.effectConfig.y = this.getSlidePosition(isForward).top;
        }
        this.start();

    },
    start: function ()
    {
        if (this.config.fadeEffect) {
            this.fadeIn();
        } else {
            this.move();
        }
    },
    fadeIn: function ()
    {
        new Effect.Fade(this.slider.up('div.slider-panel'), {
            from: 1.0,
            to:0.5,
            afterFinish: this.move.bind(this),
            beforeStart: this.effectStarts.bind(this),
            duration: 0.3
        });
    },
    fadeOut: function ()
    {
        new Effect.Fade(this.slider.up('div.slider-panel'), {
                from: 0.5,
                to:1.0,
                afterFinish: this.effectEnds.bind(this),
                duration: 0.3
        });
    },
    move: function ()
    {
        if (this.config.fadeEffect) {
            this.effectConfig.afterFinish = this.fadeOut.bind(this);
        } else {
            this.effectConfig.afterFinish = this.effectEnds.bind(this);
            this.effectConfig.beforeStart = this.effectStarts.bind(this);
        }

        new Effect.Move(this.slider, this.effectConfig);
    },
    effectStarts: function () {
        this.isPlaying = true;
    },
    effectEnds: function () {
        this.isPlaying = false;
        this.updateButtons();
    },
    getSlidePosition: function (isForward) {
        var targetOffset;
        if (isForward) {
            targetOffset = Math.min(this.items.length - this.config.pageSize, this.offset + this.config.scrollSize)
        }
        else {
            targetOffset = Math.max(this.offset - this.config.scrollSize, 0);
        }
        this.offset = targetOffset;
        var item = this.items[targetOffset];
        var itemOffset = {left:0, top:0};

        itemOffset.left = -(item.cumulativeOffset().left
                       -  this.slider.cumulativeOffset().left + this.slider.offsetLeft);
        itemOffset.top = -(item.cumulativeOffset().top
                       -  this.slider.cumulativeOffset().top + this.slider.offsetTop);
        return itemOffset;
    }
});