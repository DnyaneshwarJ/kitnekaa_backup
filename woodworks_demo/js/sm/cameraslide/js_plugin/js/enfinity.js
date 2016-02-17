/**
 * Created by Vu Van Phan on 05-05-2015.
 */
jQuery.browser = {};
(function () {
    jQuery.browser.msie = false;
    jQuery.browser.version = 0;
    if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
        jQuery.browser.msie = true;
        jQuery.browser.version = RegExp.$1;
    }
})();
function isMobile()
{
    return navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/webOS/i) || navigator.userAgent.match(/iPad/i) || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i) ? !0 : void 0
}

//function hide_success()
//{
//    jQuery("#pix_success").fadeOut(150)
//}
//
//function hide_error()
//{
//    jQuery("#pix_unsuccess").fadeOut(150)
//}

function resizeImage()
{
    w = jQuery(window).width(), h = jQuery(window).height(), jQuery(".cameraslide_container").each(function()
    //w = jQuery(window).width(), h = jQuery(window).height(), jQuery(".enfinity_imgLoaded").each(function()
    {
        var e, t, a = jQuery(this),
            i = a.attr("width"),
            r = a.attr("height"),
            o = a.parent().attr("data-align"),
            s = a.parent().attr("data-portrait");
        if ("0" == s || "off" == s)
            if (i / r < w / h)
            {
                var n = w / i,
                    u = .5 * Math.abs(h - r * n);
                switch (o)
                {
                    case "topLeft":
                        e = 0;
                        break;
                    case "topCenter":
                        e = 0;
                        break;
                    case "topRight":
                        e = 0;
                        break;
                    case "centerLeft":
                        e = "-" + u + "px";
                        break;
                    case "centerRight":
                        e = "-" + u + "px";
                        break;
                    case "bottomLeft":
                        e = "-" + 2 * u + "px";
                        break;
                    case "bottomCenter":
                        e = "-" + 2 * u + "px";
                        break;
                    case "bottomRight":
                        e = "-" + 2 * u + "px";
                        break;
                    default:
                        e = "-" + u + "px"
                }
                a.css(
                    {
                        height: r * n,
                        "margin-left": 0,
                        "margin-top": e,
                        position: "absolute",
                        visibility: "visible",
                        width: w
                    })
            }
            else
            {
                var n = h / r,
                    u = .5 * Math.abs(w - i * n);
                switch (o)
                {
                    case "topLeft":
                        t = 0;
                        break;
                    case "topCenter":
                        t = "-" + u + "px";
                        break;
                    case "topRight":
                        t = "-" + 2 * u + "px";
                        break;
                    case "centerLeft":
                        t = 0;
                        break;
                    case "centerRight":
                        t = "-" + 2 * u + "px";
                        break;
                    case "bottomLeft":
                        t = 0;
                        break;
                    case "bottomCenter":
                        t = "-" + u + "px";
                        break;
                    case "bottomRight":
                        t = "-" + 2 * u + "px";
                        break;
                    default:
                        t = "-" + u + "px"
                }
                a.css(
                    {
                        height: h,
                        "margin-left": t,
                        "margin-top": 0,
                        position: "absolute",
                        visibility: "visible",
                        width: i * n
                    })
            }
        else if (i / r < w / h)
        {
            var n = h / r,
                u = .5 * Math.abs(w - i * n);
            switch (o)
            {
                case "topLeft":
                    t = 0;
                    break;
                case "topCenter":
                    t = u + "px";
                    break;
                case "topRight":
                    t = 2 * u + "px";
                    break;
                case "centerLeft":
                    t = 0;
                    break;
                case "centerRight":
                    t = 2 * u + "px";
                    break;
                case "bottomLeft":
                    t = 0;
                    break;
                case "bottomCenter":
                    t = u + "px";
                    break;
                case "bottomRight":
                    t = 2 * u + "px";
                    break;
                default:
                    t = u + "px"
            }
            a.css(
                {
                    height: h,
                    "margin-left": t,
                    "margin-top": 0,
                    position: "absolute",
                    visibility: "visible",
                    width: i * n
                })
        }
        else
        {
            var n = w / i,
                u = .5 * Math.abs(h - r * n);
            switch (o)
            {
                case "topLeft":
                    e = 0;
                    break;
                case "topCenter":
                    e = 0;
                    break;
                case "topRight":
                    e = 0;
                    break;
                case "centerLeft":
                    e = u + "px";
                    break;
                case "centerRight":
                    e = u + "px";
                    break;
                case "bottomLeft":
                    e = 2 * u + "px";
                    break;
                case "bottomCenter":
                    e = 2 * u + "px";
                    break;
                case "bottomRight":
                    e = 2 * u + "px";
                    break;
                default:
                    e = u + "px"
            }
            a.css(
                {
                    height: r * n,
                    "margin-left": 0,
                    "margin-top": e,
                    position: "absolute",
                    visibility: "visible",
                    width: w
                })
        }
    })
}

jQuery.noConflict(),
jQuery.fn.isChildOf = function(e)
{
    return this.parents(e).length > 0
}, jQuery(function()
{
    jQuery("img").removeAttr("width").removeAttr("height")
}),

jQuery(window).bind("load resize", function()
{
    resizeImage()
}), jQuery(function()
{
    jQuery('[href*="_at_"]').each(function()
    {
        var e = jQuery(this),
            t = e.attr("href");
        t = t.replace(/\_at\_/i, "@"), e.attr("href", t)
    }), jQuery('[href*="_dot_"]').each(function()
    {
        var e = jQuery(this),
            t = e.attr("href");
        t = t.replace(/\_dot\_/i, "."), e.attr("href", t)
    })
}), jQuery(function()
{
    jQuery(".camera_wrap").length && jQuery(".camera_wrap").each(function()
    {
        var e = jQuery(this);
        e.camera(
            {
                alignment: e.attr("data-alignment"),
                autoAdvance: "true" == e.attr("data-autoadvance") ? !0 : !1,
                mobileAutoAdvance: "true" == e.attr("data-autoadvance") ? !0 : !1,
                barDirection: e.attr("data-bardirection"),
                barPosition: e.attr("data-barposition"),
                cols: parseFloat(e.attr("data-cols")),
                easing: e.attr("data-easing"),
                mobileEasing: e.attr("data-mobileeasing"),
                fx: e.attr("data-fx"),
                mobileFx: e.attr("data-mobilefx"),
                gridDifference: 0,
                height: e.attr("data-height") + e.attr("data-heightsign"),
                imagePath: plugindir + "/css/images/",
                hover: "true" == e.attr("data-hover") ? !0 : !1,
                loader: e.attr("data-loader"),
                loaderColor: e.attr("data-loadercolor"),
                loaderBgColor: e.attr("data-loaderbgcolor"),
                loaderOpacity: e.attr("data-loaderopacity"),
                loaderPadding: parseFloat(e.attr("data-loaderpadding")),
                loaderStroke: parseFloat(e.attr("data-loaderstroke")),
                minHeight: e.attr("data-minheight"),
                navigation: "true" == e.attr("data-navigation") ? !0 : !1,
                navigationHover: "true" == e.attr("data-navOnHover") ? !0 : !1,
                mobileNavHover: "true" == e.attr("data-navOnHover") ? !0 : !1,
                opacityOnGrid: "true" == e.attr("data-opacityoneffect") ? !0 : !1,
                overlayer: "pattern_none" == e.attr("data-pattern") ? !1 : !0,
                pagination: "true" == e.attr("data-pagination") ? !0 : !1,
                pauseOnClick: "true" == e.attr("data-click") ? !0 : !1,
                pieDiameter: parseFloat(e.attr("data-piediameter")),
                piePosition: e.attr("data-pieposition"),
                playPause: "true" == e.attr("data-playpause") ? !0 : !1,
                portrait: "true" == e.attr("data-portrait") ? !0 : !1,
                rows: parseFloat(e.attr("data-rows")),
                slicedCols: parseFloat(e.attr("data-slicedcols")),
                slicedRows: parseFloat(e.attr("data-slicedrows")),
                thumbnails: "true" == e.attr("data-thumbs") ? !0 : !1,
                time: parseFloat(e.attr("data-time")),
                transPeriod: parseFloat(e.attr("data-transperiod"))
            }), jQuery(".camera_overlayer", e).animate(
            {
                opacity: e.attr("data-patternopacity")
            }, 0), jQuery.isFunction(jQuery.fn.colorbox) && jQuery("a[data-box='true']", e).not(".noColorBox").each(function()
        {
            var t = jQuery(this).attr("data-box");
            jQuery(this).colorbox(
                {
                    maxWidth: "98%",
                    maxHeight: "98%",
                    scrolling: !1,
                    rel: t,
                    current: "{current} / {total}",
                    close: pix_CB_close,
                    next: pix_CB_next,
                    previous: pix_CB_prev,
                    onComplete: function()
                    {
                        jQuery("#cboxLoadedContent").prepend('<div class="cboxPrevent" />'), e.cameraPause()
                    },
                    onClosed: function()
                    {
                        jQuery(".cboxPrevent").remove(), e.cameraResume(), e.find("canvas").fadeIn()
                    }
                })
        })
    })
}), jQuery(document).bind("mobileinit", function()
{
    jQuery.mobile.ajaxEnabled = !1
}), jQuery(function()
{
    jQuery("iframe").each(function()
    {
        var e, t, a = jQuery(this);
        "percent" != a.attr("data-size") || jQuery(this).parents("div").eq(0).hasClass("letmebe") || jQuery(this).parents("div").eq(0).hasClass("pix_column_thumb") ? (a.css("width", "100%"), t = a.width(), e = a.attr("height") / a.attr("width"), a.height(t * e), jQuery(window).bind("resize", function()
        {
            t = a.width(), a.height(t * e)
        })) : (a.attr("width", a.attr("width") + "%"), t = a.width(), e = Math.round(parseInt(a.attr("height"))), a.height(.01 * t * e), jQuery(window).bind("resize", function()
        {
            t = a.width(), a.height(.01 * t * e)
        }))
    }), jQuery(".pix_wrap_player").each(function()
    {
        var e = jQuery(this),
            t = e.attr("data-height"),
            a = e.width();
        "undefined" != typeof t && t !== !1 && -1 != t.indexOf("%") && (ratio = t.replace("%", ""), e.height(.01 * a * ratio), jQuery(window).bind("resize", function()
        {
            a = e.width(), e.height(.01 * a * ratio)
        }))
    })
}), jQuery(function()
{
    jQuery("pre, code").each(function()
    {
        var e = jQuery(this).text();
        e = e.replace(/\[ /g, "["), e = e.replace(/\ ]/g, "]"), jQuery(this).text(e)
    })
}), function($)
    {
        function format(e)
        {
            for (var t = 1; t < arguments.length; t++) e = e.replace("%" + (t - 1), arguments[t]);
            return e
        }

        function CloudZoom(e, t)
        {
            var a, i, r, o, s, n, u, l, d = $("img", e),
                c = null,
                h = null,
                p = null,
                f = null,
                y = null,
                m = null,
                Q = 0,
                j = 0,
                g = 0,
                _ = 0,
                v = 0,
                b = 0,
                x = this;
            setTimeout(function()
            {
                if (null === h)
                {
                    var t = e.width();
                    e.parent().append(format('<div style="width:%0px;position:absolute;top:75%;left:%1px;text-align:center" class="cloud-zoom-loading" >Loading...</div>', t / 3, t / 2 - t / 6)).find(":last").css("opacity", .5)
                }
            }, 200);
            var w = function()
            {
                null !== m && (m.remove(), m = null)
            };
            this.removeBits = function()
            {
                p && (p.remove(), p = null), f && (f.remove(), f = null), y && (y.remove(), y = null), w(), $(".cloud-zoom-loading", e.parent()).remove()
            }, this.destroy = function()
            {
                e.data("zoom", null), h && (h.unbind(), h.remove(), h = null), c && (c.remove(), c = null), this.removeBits()
            }, this.fadedOut = function()
            {
                c && (c.remove(), c = null), this.removeBits()
            }, this.controlLoop = function()
            {
                if (p)
                {
                    var e = n - d.offset().left - .5 * o >> 0,
                        a = u - d.offset().top - .5 * s >> 0;
                    0 > e ? e = 0 : e > d.outerWidth() - o && (e = d.outerWidth() - o), 0 > a ? a = 0 : a > d.outerHeight() - s && (a = d.outerHeight() - s), p.css(
                        {
                            left: e,
                            top: a
                        }), p.css("background-position", -e + "px " + -a + "px"), j = e / d.outerWidth() * r.width >> 0, g = a / d.outerHeight() * r.height >> 0, v += (j - v) / t.smoothMove, _ += (g - _) / t.smoothMove, c.css("background-position", -(v >> 0) + "px " + (-(_ >> 0) + "px"))
                }
                Q = setTimeout(function()
                {
                    x.controlLoop()
                }, 30)
            }, this.init2 = function(e, t)
            {
                b++, 1 === t && (r = e), 2 === b && this.init()
            }, this.init = function()
            {
                $(".cloud-zoom-loading", e.parent()).remove(), h = e.parent().append(format("<div class='mousetrap' style='background-image:url();z-index:999;position:absolute;width:%0px;height:%1px;left:%2px;top:%3px;'></div>", d.outerWidth(), d.outerHeight(), 0, 0)).find(":last");
                //$(".cloud-zoom-loading", e.parent()).remove(), h = e.parent().append(format("<div class='mousetrap' style='background-image:url(\"%s\");z-index:999;position:absolute;width:%0px;height:%1px;left:%2px;top:%3px;'></div>", d.outerWidth(), d.outerHeight(), 0, 0)).find(":last");
                var a = "ontouchstart" in document.documentElement ? !0 : !1,
                    i = "touchmove mousemove",
                    j = "touchend mouseleave",
                    g = "touchstart mouseenter";
                h.bind(i, this, function(e)
                {
                    n = "undefined" != typeof e.originalEvent.touches ? e.originalEvent.touches[0].pageX : e.pageX, u = "undefined" != typeof e.originalEvent.touches ? e.originalEvent.touches[0].pageY : e.pageY
                }), h.bind(j, this, function()
                {
                    return clearTimeout(Q), p && p.fadeOut(299), f && f.fadeOut(299), y && y.fadeOut(299), c.fadeOut(300, function()
                    {
                        x.fadedOut()
                    }), !1
                }), h.bind(g, this, function(i)
                {
                    a && i.preventDefault(), n = "undefined" != typeof i.originalEvent.touches ? i.originalEvent.touches[0].pageX : i.pageX, u = "undefined" != typeof i.originalEvent.touches ? i.originalEvent.touches[0].pageY : i.pageY, l = i.data, c && (c.stop(!0, !1), c.remove());
                    var Q = t.adjustX,
                        j = t.adjustY,
                        g = d.outerWidth(),
                        _ = d.outerHeight(),
                        v = t.zoomWidth,
                        b = t.zoomHeight;
                    "auto" == t.zoomWidth && (v = g), "auto" == t.zoomHeight && (b = _);
                    var x = e.parent();
                    switch (t.position)
                    {
                        case "top":
                            j -= b;
                            break;
                        case "right":
                            Q += g;
                            break;
                        case "bottom":
                            j += _;
                            break;
                        case "left":
                            Q -= v;
                            break;
                        case "inside":
                            v = g, b = _;
                            break;
                        default:
                            x = $("#" + t.position), x.length ? (v = x.innerWidth(), b = x.innerHeight()) : (x = e, Q += g, j += _)
                    }
                    c = x.append(format('<div id="cloud-zoom-big" class="cloud-zoom-big" style="display:none;position:absolute;left:%0px;top:%1px;width:%2px;height:%3px;background-image:url();z-index:99;"></div>', Q, j, v, b, r.src)).find(":last"), d.attr("title") && t.showTitle && c.append(format('<div class="cloud-zoom-title">%0</div>', d.attr("title"))).find(":last").css("opacity", t.titleOpacity), $.browser.msie && $.browser.version < 7 && (m = $('<iframe frameborder="0" src="#"></iframe>').css(
                    //c = x.append(format('<div id="cloud-zoom-big" class="cloud-zoom-big" style="display:none;position:absolute;left:%0px;top:%1px;width:%2px;height:%3px;background-image:url(\'%s\');z-index:99;"></div>', Q, j, v, b, r.src)).find(":last"), d.attr("title") && t.showTitle && c.append(format('<div class="cloud-zoom-title">%0</div>', d.attr("title"))).find(":last").css("opacity", t.titleOpacity), $.browser.msie && $.browser.version < 7 && (m = $('<iframe frameborder="0" src="#"></iframe>').css(
                        {
                            position: "absolute",
                            left: Q,
                            top: j,
                            zIndex: 99,
                            width: v,
                            height: b
                        }).insertBefore(c)), c.fadeIn(500), p && (p.remove(), p = null), o = d.outerWidth() / r.width * c.width(), s = d.outerHeight() / r.height * c.height(), p = e.append(format("<div class = 'cloud-zoom-lens' style='display:none;z-index:98;position:absolute;width:%0px;height:%1px;'></div>", o, s)).find(":last"), h.css("cursor", p.css("cursor"));
                    var w = !1;
                    t.tint && (p.css("background", 'url("' + d.attr("src") + '")'), f = e.append(format('<div style="display:none;position:absolute; left:0px; top:0px; width:%0px; height:%1px; background-color:%2;" />', d.outerWidth(), d.outerHeight(), t.tint)).find(":last"), f.css("opacity", t.tintOpacity), w = !0, f.fadeIn(500)), t.softFocus && (p.css("background", 'url("' + d.attr("src") + '")'), y = e.append(format('<div style="position:absolute;display:none;top:2px; left:2px; width:%0px; height:%1px;" />', d.outerWidth() - 2, d.outerHeight() - 2, t.tint)).find(":last"), y.css("background", 'url("' + d.attr("src") + '")'), y.css("opacity", .5), w = !0, y.fadeIn(500)), w || p.css("opacity", t.lensOpacity), "inside" !== t.position && p.fadeIn(500), l.controlLoop()
                })
            }, a = new Image, $(a).load(function()
            {
                x.init2(this, 0)
            }), a.src = d.attr("src"), i = new Image, $(i).load(function()
            {
                x.init2(this, 1)
            }), i.src = e.attr("href")
        }
        $.fn.CloudZoom = function(options)
        {
            try
            {
                document.execCommand("BackgroundImageCache", !1, !0)
            }
            catch (e)
            {}
            return this.each(function()
            {
                var relOpts, opts;
                if (eval("var	a = {" + $(this).attr("rel") + "}"), relOpts = a, $(this).is(".cloud-zoom")) $(this).css(
                    {
                        position: "relative",
                        display: "block"
                    }), $("img", $(this)).css(
                    {
                        display: "block"
                    }), "wrap" != $(this).parent().attr("id") && $(this).wrap('<div id="wrap" style="top:0px;z-index:1;position:relative;"></div>'), opts = $.extend(
                    {}, $.fn.CloudZoom.defaults, options), opts = $.extend(
                    {}, opts, relOpts), $(this).data("zoom", new CloudZoom($(this), opts));
                else if ($(this).is(".cloud-zoom-gallery"))
                {
                    opts = $.extend(
                        {}, relOpts, options), $(this).data("relOpts", opts);
                    var t = $(this);
                    t.bind("touchstart click", t, function(e)
                    {
                        var a = e.data.data("relOpts");
                        return $("#" + a.useZoom).data("zoom").destroy(), $("#" + a.useZoom).attr("href", e.data.attr("href")), $("#" + a.useZoom + " img").attr("src", e.data.data("relOpts").smallImage), $("#" + e.data.data("relOpts").useZoom).CloudZoom(), $(".cloud-zoom-gallery").removeClass("zoomSelected"), t.addClass("zoomSelected"), !1
                    })
                }
            }), this
        }, $.fn.CloudZoom.defaults = {
            zoomWidth: "auto",
            zoomHeight: "auto",
            position: "right",
            tint: !1,
            tintOpacity: .5,
            lensOpacity: .5,
            softFocus: !1,
            smoothMove: 3,
            showTitle: !0,
            titleOpacity: .5,
            adjustX: 0,
            adjustY: 0
        }
    }(jQuery),
    function(e)
    {
        function t(e)
        {
            for (var t, a = ["transform", "WebkitTransform", "msTransform", "MozTransform", "OTransform"]; t = a.shift();)
                if ("undefined" != typeof e.style[t]) return t;
            return "transform"
        }
        var a = null,
            i = e.fn.css;
        e.fn.css = function(r, o)
        {
            if (null === a && (a = "undefined" != typeof e.cssProps ? e.cssProps : "undefined" != typeof e.props ? e.props :
                {}), "undefined" == typeof a.transform && ("transform" == r || "object" == typeof r && "undefined" != typeof r.transform) && (a.transform = t(this.get(0))), "transform" != a.transform)
                if ("transform" == r)
                {
                    if (r = a.transform, "undefined" == typeof o && jQuery.style) return jQuery.style(this.get(0), r)
                }
                else "object" == typeof r && "undefined" != typeof r.transform && (r[a.transform] = r.transform, delete r.transform);
            return i.apply(this, arguments)
        }
    }(jQuery),
    function(e)
    {
        function t(e)
        {
            var t = e.data("_ARS_data");
            return t || (t = {
                rotateUnits: "deg",
                scale: 1,
                rotate: 0
            }, e.data("_ARS_data", t)), t
        }

        function a(e, t)
        {
            e.css("transform", "rotate(" + t.rotate + t.rotateUnits + ") scale(" + t.scale + "," + t.scale + ")")
        }
        e.fn.rotate = function(i)
        {
            var r, o = e(this),
                s = t(o);
            return "undefined" == typeof i ? s.rotate + s.rotateUnits : (r = i.toString().match(/^(-?\d+(\.\d+)?)(.+)?$/), r && (r[3] && (s.rotateUnits = r[3]), s.rotate = r[1], a(o, s)), this)
        }, e.fn.scale = function(i)
        {
            var r = e(this),
                o = t(r);
            return "undefined" == typeof i ? o.scale : (o.scale = i, a(r, o), this)
        };
        var i = e.fx.prototype.cur;
        e.fx.prototype.cur = function()
        {
            return "rotate" == this.prop ? parseFloat(e(this.elem).rotate()) : "scale" == this.prop ? parseFloat(e(this.elem).scale()) : i.apply(this, arguments)
        }, e.fx.step.rotate = function(a)
        {
            var i = t(e(a.elem));
            e(a.elem).rotate(a.now + i.rotateUnits)
        }, e.fx.step.scale = function(t)
        {
            e(t.elem).scale(t.now)
        };
        var r = e.fn.animate;
        e.fn.animate = function(a)
        {
            if ("undefined" != typeof a.rotate)
            {
                var i, o, s = a.rotate.toString().match(/^(([+-]=)?(-?\d+(\.\d+)?))(.+)?$/);
                s && s[5] && (i = e(this), o = t(i), o.rotateUnits = s[5]), a.rotate = s[1]
            }
            return r.apply(this, arguments)
        }
    }(jQuery),
    function($)
    {
        $.fn.filmore = function(opts, callback)
        {
            function shuffle(e)
            {
                for (var t, a, i = e.length; i; t = parseInt(Math.random() * i), a = e[--i], e[i] = e[t], e[t] = a);
                return e
            }

            function thisCheckImages(e)
            {
                function t(e, r)
                {
                    var o = a[r];
                    $("<img />").load(function()
                    {

                        if (r += 1, i > r)
                        {
                            clearTimeout(a);
                            var a = setTimeout(function()
                            {
                                t(e, r)
                            }, 100)
                        }
                        else target.find(".filmoreSlide_" + e).addClass("filmoreLoaded"), target.find(".filmoreSlide_" + e + ' > div[data-use="background"]').not(".dataLoaded").each(function()
                        {
                            var t = $(this),
                                a = $(this).attr("data-src");
                            t.addClass("dataLoaded"), t.append('<img src="' + a + '" />'), slideW = target.outerWidth(), target.css(
                                {
                                    height: slideW / (slideStartW / slideStartH)
                                }), slideH = target.outerHeight(), $("<img />").load(function()
                            {
                                var a = $(this),
                                    r = a.get(0).width,
                                    o = a.get(0).height;
                                if (t.find("img").css(
                                        {
                                            position: "absolute",
                                            width: "100%" // làm cho ảnh slide background lớn full màn hình
                                        }).addClass("filmoreLoaded"), target.find(".filmoreSlide_" + e + " > div[data-src].filmoreLoaded").length == i && target.find(".filmoreSlide_" + e).addClass("filmoreLoaded"), slideStartW / slideStartH > r / o)
                                {
                                    var s = slideW / r,
                                        n = .5 * Math.abs(slideH - o * s),
                                        u = "-" + parseFloat(n) / slideStartH * 100 + "%";
                                    t.find("img").css(
                                        {
                                            left: 0,
                                            right: 0,
                                            top: u,
                                            width: "100%" // làm cho ảnh slide background lớn full màn hình
                                        }).attr("data-width", "100%")
                                }
                                else
                                {
                                    var s = slideStartH / o,
                                        n = .5 * Math.abs(slideStartW - r * s),
                                        l = "-" + parseFloat(n) / slideStartW * 100 + "%";
                                    t.find("img").css(
                                        {
                                            left: l,
                                            right: l,
                                            top: 0,
                                            width: parseFloat(r * s) / slideStartW * 100 + 1 + "%"
                                        }).attr("data-width", parseFloat(r * s) / slideStartW * 100 + 1 + "%")
                                }
                            }).attr("src", a)
                        }), target.find(".filmoreSlide_" + e + ' > div[data-use="video"]').not(".dataLoaded").each(function()
                        {
                            var t = $(this),
                                a = "" != $(this).attr("data-src") ? $(this).attr("data-src") : enfinity_theme_dir + "/images/blank.gif";
                            t.addClass("dataLoaded"), t.append('<div class="imgFake" />');
                            var r = $(".imgFake", t);
                            r.css(
                                {
                                    position: "absolute",
                                    bottom: 0,
                                    top: 0,
                                    right: 0,
                                    left: 0,
                                    width: "100%",
                                    height: "100%",
                                    overflow: "hidden"
                                }), $("<img />").load(function()
                            {
                                r.css(
                                    {
                                        backgroundImage: "url(" + a + ")",
                                        backgroundRepeat: "no-repeat",
                                        backgroundSize: "cover",
                                        backgroundPosition: "center"
                                    }), t.addClass("filmoreLoaded"), target.find(".filmoreSlide_" + e + " > div[data-src].filmoreLoaded").length == i && target.find(".filmoreSlide_" + e).addClass("filmoreLoaded")
                            }).attr("src", a)
                        }), target.find(".filmoreSlide_" + e + ' > div[data-use="simple"]').not(".dataLoaded").each(function()
                        {
                            var t = $(this),
                                a = $(this).attr("data-src"),
                                r = $("> a", this).length ? $("> a", this) : $(this);
                            $(this).addClass("dataLoaded"), r.append('<img src="' + a + '" />'), $("<img />").load(function()
                            {
                                var a = $(this),
                                    r = parseFloat(a.naturalWidth()) / slideStartW * 100;
                                t.css(
                                    {
                                        width: r + "%"
                                    }).addClass("filmoreLoaded"), target.find(".filmoreSlide_" + e + " > div[data-src].filmoreLoaded").length == i && target.find(".filmoreSlide_" + e).addClass("filmoreLoaded")
                            }).attr("src", a)
                        }), thisCheckImages(e + 1)
                    }).attr("src", o)
                }
                if (arrSlides[e] && !target.find(".filmoreSlide_" + e).hasClass("filmoreLoaded"))
                {
                    var a = new Array;
                    a.push(arrSlides[e]), $.each(arrImages[e], function(e, t)
                    {
                        a.push(t)
                    });
                    var i = a.length;
                    t(e, 0)
                }
            }

            function canvasLoader()
            {
                rad = 0, jQuery.browser.msie && jQuery.browser.version < 9 || ctx.clearRect(0, 0, opts.pieDiameter, opts.pieDiameter)
            }

            function checkIframe(e, t)
            {
                $("iframe[src]", target).length ? $("iframe[src]", target).each(function()
                {
                    var a = $(this),
                        i = a.attr("src");
                    a.attr("data-src", i);
                    var r = a.parents("div").eq(0);
                    r.find(".imgFake").show(), a.fadeOut(400, function()
                    {
                        clearInterval(intval), clearTimeout(iframeTimeOut), a.removeAttr("src"), iframeTimeOut = setTimeout(function()
                        {
                            nextSlide(e, t)
                        }, 500)
                    })
                }) : nextSlide(e, t)
            }

            function nextSlide(navSlide, direction) {
                function slideTheRest() {
                    slide.find('div[data-use="simple"], div[data-use="video"], div[data-use="caption"], div[data-use="html"]').each(function () {
                        var objW = $(this).outerWidth(),
                            objH = $(this).outerHeight(),
                            dataTime = "undefined" != $(this).attr("data-time") ? parseFloat($(this).attr("data-time")) : opts.transPeriod,
                            thisDelay = "undefined" != $(this).attr("data-delay") ? parseFloat($(this).attr("data-delay")) : 0,
                            thisEaseIn = "undefined" != $(this).attr("data-easein") ? $(this).attr("data-easein") : "linear",
                            thisFxIn = "undefined" != $(this).attr("data-fxin") ? $(this).attr("data-fxin") : "",
                            thisFadeIn = "undefined" != $(this).attr("data-fadein") && "false" != $(this).attr("data-fadein") ? $(this).attr("data-fadein") : "0",
                            thisCss, thisAnim;
                        eval("var css = {" + $(this).attr("data-style") + "}"), $(this).show().css(css);
                        var leftPercent = parseFloat($(this).css("left")) / slideStartW * 100,
                            topPercent = parseFloat($(this).css("top")) / slideStartH * 100;
                        switch (thisFxIn) {
                            case "fromtop":
                                thisCss = "top: '-'+(((parseFloat(objH)/slideH)*100)+1)+'%', left: leftPercent+'%'", thisAnim = "top: topPercent+'%', opacity:1";
                                break;
                            case "fromright":
                                thisCss = "left: '100%', top: topPercent+'%'", thisAnim = "left: leftPercent+'%', opacity:1";
                                break;
                            case "frombottom":
                                thisCss = "top: '100%', left: leftPercent+'%'", thisAnim = "top: topPercent+'%', opacity:1";
                                break;
                            case "fromleft":
                                thisCss = "left: '-'+(((parseFloat(objW)/slideW)*100)+1)+'%', top: topPercent+'%'", thisAnim = "left: leftPercent+'%', opacity:1";
                                break;
                            default:
                                thisCss = "left: leftPercent+'%', top: topPercent+'%'", thisAnim = "opacity:1"
                        }
                        eval("thisCss = {" + thisCss + ', visibility:"visible"}'), eval("thisAnim = {" + thisAnim + "}"), "0" != thisFadeIn ? $(this).animate(
                            {
                                opacity: 0
                            }, 0) : $(this).animate(
                            {
                                opacity: 1
                            }, 0), $(this).css(thisCss), $(this).stop(!0, !0).delay(thisDelay).animate(thisAnim, dataTime, thisEaseIn), "undefined" != $(this).attr("data-rotatein") && "false" != $(this).attr("data-rotatein") && ($(".filmore_rotate_wrap", this).animate(
                            {
                                rotate: "0"
                            }, 0), $(".filmore_rotate_wrap", this).length || ($(this).wrapInner('<div class="filmore_rotate_wrap" />'), $(".filmore_rotate_wrap", this).animate(
                            {
                                rotate: "180deg"
                            }, 0)), $(".filmore_rotate_wrap", this).delay(thisDelay).animate(
                            {
                                rotate: "360deg"
                            }, dataTime, thisEaseIn))
                    })
                }

                clearInterval(intval), rad = 0, target.addClass("filmoresliding");
                var slideI;
                switch (slideI = 0 > navSlide ? amountSlide - 1 : navSlide >= amountSlide ? 0 : navSlide, direction = "" == direction ? "next" : direction) {
                    case "prev":
                        minusSign = "+", plusSign = "-";
                        break;
                    default:
                        minusSign = "-", plusSign = "+"
                }
                var slide = $(".filmoreSlide:eq(" + slideI + ")", target),
                    current = $(".filmoreSlide.filmoreCurrent", target);
                if (opts.pagination.length && (opts.pagination.find("a.filmore_pag").removeClass("filmore_current_pag"),
                    opts.pagination.find("a.filmore_pag.filmore_pag_" + slideI).addClass("filmore_current_pag")),
                    clearTimeout(slideTryAgain),
                arrSlides[slideI] && target.find(".filmoreSlide_" + slideI).hasClass("filmoreLoaded"))
                {
                $("div.filmoreSlide", target).css(
                    {
                        zIndex: 0
                    }), alreadyStarted ? slide.css(
                    {
                        left: plusSign + "100%",
                        zIndex: 1
                    }).show().delay(300).animate(
                    {
                        left: "0%"
                    }, opts.transPeriod, opts.easing, function () {
                        $("div.filmoreSlide", target).removeClass("filmoreCurrent"), $(this).addClass("filmoreCurrent").addClass("filmoreLoaded"), $("div.filmoreSlide", target).not(".filmoreCurrent").hide(), target.removeClass("filmoresliding"), filmoreTimer(slideI + 1)
                    }).find('div[data-use="background"]').each(function () {
                        $(this).css(
                            {
                                left: minusSign + "80%"
                            });
                        {
                            var e = ["0", "auto", "0", "auto"],
                                t = shuffle(e),
                                a = shuffle(e);
                            t[0], t[1], a[2], a[3]
                        }
                        $(this).stop(!0, !0).delay(300).animate(
                            {
                                left: "0%"
                            }, opts.transPeriod, opts.easing)
                    }) : (slide.find('div[data-use="simple"], div[data-use="video"], div[data-use="caption"], div[data-use="html"]').each(function () {
                    var objW = $(this).outerWidth(),
                        objH = $(this).outerHeight(),
                        dataTime = "undefined" != $(this).attr("data-time") ? parseFloat($(this).attr("data-time")) : opts.transPeriod,
                        thisDelay = "undefined" != $(this).attr("data-delay") ? parseFloat($(this).attr("data-delay")) : 0;
                    eval("var css = {" + $(this).attr("data-style") + "}"), $(this).css(
                        {
                            visibility: "hidden"
                        }).css(css)
                }), target.removeClass("pix_slideshow_preloading"), slide.fadeIn(500, function () {
                    slideTheRest(), $(this).addClass("filmoreCurrent").addClass("filmoreLoaded"), target.removeClass("filmoresliding"), alreadyStarted = !0, filmoreTimer(slideI + 1)
                })), current.find('div[data-use="background"]').each(function () {
                    $(this).css(
                        {
                            left: "0%"
                        }), $(this).delay(300).animate(
                        {
                            left: minusSign + "20%"
                        }, opts.transPeriod, opts.easing)
                }), current.find('div[data-use="simple"], div[data-use="video"], div[data-use="caption"], div[data-use="html"]').each(function () {
                    var objW = $(this).outerWidth(),
                        objH = $(this).outerHeight(),
                        thisEaseOut = "undefined" != $(this).attr("data-easeout") ? $(this).attr("data-easeout") : "linear",
                        thisFxOut = "undefined" != $(this).attr("data-fxout") ? $(this).attr("data-fxout") : "",
                        thisFadeOut = "undefined" != $(this).attr("data-fadeout") && "false" != $(this).attr("data-fadeout") ? "0" : "1",
                        thisAnim;
                    switch (thisFxOut) {
                        case "totop":
                            thisAnim = "top: '-'+(((parseFloat(objH)/slideH)*100)+1)+'%', opacity: thisFadeOut";
                            break;
                        case "toright":
                            thisAnim = "left: '100%', opacity: thisFadeOut";
                            break;
                        case "tobottom":
                            thisAnim = "top: '100%', opacity: thisFadeOut";
                            break;
                        case "toleft":
                            thisAnim = "left: '-'+(((parseFloat(objW)/slideW)*100)+1)+'%', opacity: thisFadeOut";
                            break;
                        default:
                            thisAnim = "opacity: thisFadeOut"
                    }
                    eval("thisAnim = {" + thisAnim + "}");
                    var index = $(this).index(),
                        leng = current.find('div[data-use="simple"], div[data-use="video"], div[data-use="caption"], div[data-use="html"]').length;
                    "undefined" != $(this).attr("data-rotatein") && "false" != $(this).attr("data-rotatein") && ($(".filmore_rotate_wrap", this).animate(
                        {
                            rotate: "0"
                        }, 0), $(".filmore_rotate_wrap", this).length || $(this).wrapInner('<div class="filmore_rotate_wrap" />'), $(".filmore_rotate_wrap", this).stop(!0, !1).delay(30 * index).animate(
                        {
                            rotate: "180deg"
                        }, .5 * opts.transPeriod, thisEaseOut)), $(this).stop(!0, !1).delay(30 * index).animate(thisAnim, .5 * opts.transPeriod, thisEaseOut)
                }), alreadyStarted && slideTheRest();
            }
                else var slideTryAgain = setTimeout(function()
                {
                    nextSlide(slideI)
                }, 100)
            }

            function filmoreTimer(e)
            {
                var t = .01;
                intval = setInterval(function()
                {
                    radNew = rad, jQuery.browser.msie && jQuery.browser.version < 9 || (ctx.clearRect(0, 0, opts.pieDiameter, opts.pieDiameter), ctx.globalCompositeOperation = "destination-over", ctx.beginPath(), ctx.arc(opts.pieDiameter / 2, opts.pieDiameter / 2, opts.pieDiameter / 2 - opts.loaderStroke, 0, 2 * Math.PI, !1), ctx.lineWidth = opts.loaderStroke, ctx.strokeStyle = opts.loaderBgColor, ctx.stroke(), ctx.closePath(), ctx.globalCompositeOperation = "source-over", ctx.beginPath(), ctx.arc(opts.pieDiameter / 2, opts.pieDiameter / 2, opts.pieDiameter / 2 - opts.loaderStroke, 0, 2 * Math.PI * radNew, !1), ctx.lineWidth = opts.loaderStroke - 2 * opts.loaderPadding, ctx.strokeStyle = opts.loaderColor, ctx.stroke(), ctx.closePath()), 1.002 >= rad && !target.hasClass("filmore_stopped") && !target.hasClass("filmore_hovered") ? rad += t : 1 >= rad && (target.hasClass("filmore_stopped") || target.hasClass("filmore_hovered")) ? rad = rad : target.hasClass("filmore_stopped") || target.hasClass("filmore_hovered") || checkIframe(e)
                }, opts.time * t)
            }
            var pix_pie_bg = "#a3a3a3";
            var pix_pie_stroke = "#ffffff";
            var enfinity_content_width = "930";

            var target = $(this).wrapInner('<div class="film_slide" />'),
                defaults = {
                    time: 8e3,
                    transPeriod: 800,
                    easing: "easeInOutQuad",
                    prev: $("#prev"),
                    next: $("#next"),
                    pause: $("#pause"),
                    play: $("#play"),
                    pagination: $("#pagination"),
                    loader: $("#loader"),
                    autoadv: !0,
                    hover: !0,
                    pieDiameter: 25,
                    loaderStroke: 7,
                    loaderColor: pix_pie_stroke,
                    loaderBgColor: pix_pie_bg,
                    loaderPadding: 2
                },
                opts = $.extend(
                    {}, defaults, opts),
                wrap = target.find(".film_slide"),
                arrSlides = new Array,
                arrImages = new Array;
            $(" > div", wrap).each(function()
            {
                var e = $(this).index();
                $(this).addClass("filmoreSlide").addClass("filmoreSlide_" + e), arrImages[e] = new Array, $(" > div", wrap).eq(e).find('div[data-use="background"]').each(function()
                {
                    var e = $(this).attr("data-src");
                    if (-1 != e.indexOf("?")) var t = "&";
                    else var t = "?";
                    $(this).addClass("filmoreBgs"), e = e + t + "filmoretime=" + (new Date).getTime(), arrSlides.push(e), $(this).attr("data-src", e)
                }), $(" > div", wrap).eq(e).find('div[data-use="simple"], div[data-use="video"]').each(function()
                {
                    var t = $(this).attr("data-src");
                    if (-1 != t.indexOf("?")) var a = "&";
                    else var a = "?";
                    t = t + a + "filmoretime=" + (new Date).getTime(), arrImages[e].push(t), $(this).attr("data-src", t)
                })
            });
            var slideStartW = target.outerWidth(),
                slideStartH = target.outerHeight();
            target.css(
                {
                    width: "100%"
                });
            var slideW = target.outerWidth();
            target.css(
                {
                    height: slideStartW * slideStartH / slideW
                });
            var slideH = target.outerHeight();
            $(".filmore_caption *").each(function()
            {
                {
                    var e = $(this),
                        t = parseFloat(e.css("font-size"));
                    parseFloat(e.css("line-height")), parseFloat(e.css("margin")), parseFloat(e.css("padding"))
                }
                $(window).bind("load resize", function()
                {
                    var a = slideW / enfinity_content_width * t;
                    e.css(
                        {
                            fontSize: a + "px"
                        })
                })
            });
            var captionFontSize = parseFloat($(".filmore_caption").css("font-size")),
                captionLineHeight = parseFloat($(".filmore_caption").css("line-height"));
            $(window).bind("load resize", function()
            {
                slideW = target.outerWidth(), target.css(
                    {
                        height: slideW / (slideStartW / slideStartH)
                    }), slideH = target.outerHeight();
                var e = slideW / enfinity_content_width * captionFontSize,
                    t = slideH / slideStartH * captionLineHeight;
                $(".filmore_caption").css(
                    {
                        fontSize: e + "px",
                        lineHeight: t + "px"
                    })
            }), opts.pagination && $.each(arrSlides, function(e)
            {
                $(opts.pagination).append('<a href="#" class="filmore_pag filmore_pag_' + e + '" data-pag="' + e + '">' + e + "</a>")
            });
            var amountSlide = arrSlides.length;
            thisCheckImages(0, 0), $("iframe[src]", target).each(function()
            {
                var e = $(this).css(
                        {
                            position: "absolute",
                            top: 0,
                            left: 0
                        }),
                    t = e.attr("src"); - 1 == t.indexOf("autoplay") && (-1 != t.indexOf("vimeo") || -1 != t.indexOf("youtube") ? autoplay = -1 != t.indexOf("?") ? "&autoplay=1" : "?autoplay=1" : -1 != t.indexOf("dailymotion") && (autoplay = -1 != cloneSrc.indexOf("?") ? "&autoPlay=1" : "?autoPlay=1")), e.attr("data-src", t + autoplay), t = e.attr("data-src");
                var a = e.parents("div").eq(0),
                    i = e.attr("width"),
                    r = e.attr("height");
                a.css(
                    {
                        width: i,
                        height: r
                    }), e.attr("width", "100%").attr("height", "100%").removeAttr("src")
            }), target.css(
                {
                    visibility: "visible"
                });
            //var pieID = "pie_" + wrap.index();
            var pieID = "filmore_loader_" + wrap.index();
            opts.loader.append('<canvas id="' + pieID + '"></canvas>');
            var G_vmlCanvasManager, canvas = document.getElementById(pieID);
            canvas.setAttribute("width", 40), canvas.setAttribute("height", 40);
            var rad, radNew;
            if (canvas && canvas.getContext)
            {
                var ctx = canvas.getContext("2d");
                ctx.rotate(1.5 * Math.PI), ctx.translate(-opts.pieDiameter, 0)
            }
            canvasLoader();
            var alreadyStarted = !1, rad, intval, iframeTimeOut;
            checkIframe(0),
            0 == opts.autoadv && target.addClass("filmore_stopped"),
            1 == opts.hover && target.hover(function()
            {
                target.addClass("filmore_hovered")
            }, function()
            {
                target.removeClass("filmore_hovered")
            }),
                //$(".imgFake").die("click"),
                    $(".imgFake").on("click", function()
            {
                var e = $(this).parents("div").eq(0),
                    t = $("iframe", e).attr("data-src");
                $("iframe", e).attr("src", t).show(),
                    target.addClass("filmore_stopped"),
                    $(this).hide()
            }),
            opts.prev.length && opts.prev.bind("click", function()
            {
                if (!target.hasClass("filmoresliding"))
                {
                    var e = parseFloat($("div.filmoreSlide.filmoreCurrent", target).index(".filmoreSlide"));
                    checkIframe(e - 1, "prev")
                }
                return !1
            }), opts.next.length && opts.next.bind("click", function()
            {
                if (!target.hasClass("filmoresliding"))
                {
                    var e = parseFloat($("div.filmoreSlide.filmoreCurrent", target).index(".filmoreSlide"));
                    checkIframe(e + 1)
                }
                return !1
            }), target.on("swipeleft", function()
            {
                if (!target.hasClass("filmoresliding"))
                {
                    var e = parseFloat($("div.filmoreSlide.filmoreCurrent", target).index(".filmoreSlide"));
                    checkIframe(e + 1, "next")
                }
            }), target.on("swiperight", function()
            {
                if (!target.hasClass("filmoresliding"))
                {
                    var e = parseFloat($("div.filmoreSlide.filmoreCurrent", target).index(".filmoreSlide"));
                    checkIframe(e - 1, "prev")
                }
            }),
            opts.pause.length && opts.pause.bind("click", function()
            {
                return target.addClass("filmore_stopped"), $(this).fadeOut(50, function()
                {
                    opts.play.fadeIn(50)
                }), !1
            }), opts.play.length && opts.play.bind("click", function()
            {
                return target.removeClass("filmore_stopped"), $(this).fadeOut(50, function()
                {
                    opts.pause.fadeIn(50)
                }), !1
            }), $("a.filmore_pag", opts.pagination).on("click", function()
            {
                if (!target.hasClass("filmoresliding"))
                {
                    var e = parseFloat($("div.filmoreSlide.filmoreCurrent", target).index(".filmoreSlide")),
                        t = parseFloat($(this).attr("data-pag"));
                    if (t > e) var a = "next";
                    else var a = "prev";
                    e != t && checkIframe(t, a)
                }
                return !1
            })
        }
    }(jQuery), jQuery(function()
{
    jQuery(".pix_slideshow").each(function()
    {
        var e = jQuery(".pix_slideshow_target", this),
            t = parseFloat(e.attr("data-time")),
            a = parseFloat(e.attr("data-transperiod")),
            i = "true" == e.attr("data-prevnext") ? jQuery(".filmore_prev", this) : "",
            r = "true" == e.attr("data-prevnext") ? jQuery(".filmore_next", this) : "",
            o = "true" == e.attr("data-playpause") ? jQuery(".filmore_pause", this) : "",
            s = "true" == e.attr("data-playpause") ? jQuery(".filmore_play", this) : "",
            n = "true" == e.attr("data-pagination") ? jQuery(".filmore_pagination", this) : "",
            //u = jQuery(".camera_pie", this),
            u = jQuery(".filmore_loader", this),
            l = "true" == e.attr("data-autoadvance") ? !0 : !1;
        e.filmore(
            {
                time: t,
                transPeriod: a,
                prev: i,
                next: r,
                pause: o,
                play: s,
                pagination: n,
                loader: u,
                autoadv: l
            })
    })
}), jQuery(function()
{
    jQuery(".demo_store").length && (jQuery("body").addClass("wc-demo"), jQuery(window).bind("load resize", function()
    {
        var e = jQuery(".demo_store").outerHeight();
        jQuery(".wc-demo #enfinity_body, .wc-demo #pix_topiconbar").css(
            {
                top: e
            })
    }))
}), jQuery(function()
{
    jQuery('a[href=""]').click(function()
    {
        return !1
    })
})