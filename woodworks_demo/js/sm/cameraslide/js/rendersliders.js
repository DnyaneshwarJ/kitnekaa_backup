/**
 * @package SM Camera Slideshow
 * @version 2.0.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright Copyright (c) 2014 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.magentech.com
 */
"use strict";
var slideHideMasrginLeftRight = function slideHideMarginLeftRight() {
	var mg_l = $('cameraslide_position_margin_left');
	var mg_r = $('cameraslide_position_margin_right');
	var a = $('cameraslide_position_position_alignment').value;
	switch (a) {
		case 'topLeft':
		case 'topCenter':
		case 'topRight':
		case 'centerLeft':
		case 'centerRight':
		case 'bottomLeft':
		case 'bottomCenter':
		case 'bottomRight':
			mg_l.disabled = false;
			mg_l.removeClassName('disabled');
			mg_l.value = 0;
			mg_r.disabled = false;
			mg_r.removeClassName('disabled');
			mg_r.value = 0;
			break;
		case 'center':
			mg_l.disabled = true;
			mg_l.addClassName('disabled');
			mg_r.disabled = true;
			mg_r.addClassName('disabled');
			break;
	}
};

var CameraSlide = CameraSlide || {};
CameraSlide = Class.create();
CameraSlide.prototype = {
	form: null,
	time_load: null,
	container: null,
	list: null,
	sliders: null,
	count: 0,
	index: 0,
	layers: {},
	layerParams: "time_transitions|time_delay_transitions|width|height|min_width|min_height|bg_color|color|top|left|class|alt_image|title_image_link|target_link|link|text|data_easein|data_easeout|data_fxin|data_fxout|data_fadein|data_fadeout|text_align|font_family|font_size|textUnderline|textBold|textItalic".split("|"),
	videoParams: "width|height|fullwidth|loop|controls|args|autoplay|muted".split("|"),
	slideParams: "",
	cssState: "normal",
	cssUsingHover: 2,
	styles: new Hash(),
	autorun: true,
	layerParamsElm: {},
	selectedLayer: null,
	deleteBtn: null,
	dupLayerBtn: null,
	editLayerBtn: null,
	videoData: null,
	lastVideoId: null,
	videoSearch: false,
	initialize: function(a, b, c) {
		this.mediaUrl = c.media_url;
		this.form = a;
		this.time_load = b || 9e3;
		this.collectContainer();
		this.updateContainer();
		this.updateList();
		this.selectLayer();
		this.collectBtns();
		this.collectParamsElement();
	},
	updateCssElementsEditor: function(a) {
		var b = this;

		function c(a, c) {
			a.on("slide", function(a, d) {
				var e = jQuery(this).data("sid");
				if ("normal" == b.cssState) {
					if (!b.cssObject[c]) b.cssObject[c] = [0, 0, 0, 0];
					b.cssObject[c][e] = d.value;
				} else {
					if (!b.cssHover[c]) b.cssHover[c] = [0, 0, 0, 0];
					b.cssHover[c][e] = d.value;
				}
				b.updateCssPreview();
			});
		}

		function d(a, c) {
			var d = {},
				f = a.val() ? e(a.val(), parseInt(c) / 100) : "";
			d["background-color"] = f ? "rgba(" + f + ")" : "transparent";
			if ("normal" == b.cssState) b.cssObject["background-color"] = d["background-color"];
			else b.cssHover["background-color"] = d["background-color"];
			b.updateCssPreview();
		}

		function e(a, b) {
			var c = parseInt(a, 16),
				d = c >> 16 & 255,
				e = c >> 8 & 255,
				f = 255 & c,
				g = b < 0 ? 0 : b > 1 ? 1 : b;
			return [d, e, f, g].join();
		}

		function f(a, b, c) {
			return ((1 << 24) + (a << 16) + (b << 8) + c).toString(16).slice(1);
		}
		this.cssParams.each(function(e) {
			switch (e) {
				case "padding":
				case "border-radius":
					if (a[e])
						for (var g in a[e]) {
							var h = jQuery("#css_" + e + "_" + g);
							if (h.length) {
								h.slider("option", "value", a[e][g]);
								if (!h.data("binded")) {
									h.data("binded", true);
									c(h, e);
								}
							}
						} else
						for (var g = 0; g <= 4; g++) {
							var h = jQuery("#css_" + e + "_" + g);
							if (h.length) {
								h.slider("option", "value", 0);
								if (!h.data("binded")) {
									h.data("binded", true);
									c(h, e);
								}
							}
						}
					break;

				case "font-size":
				case "line-height":
				case "font-weight":
				case "border-width":
					var h = jQuery("#css_" + e);
					if (h.length) {
						a[e] = a[e] || 0;
						h.slider("option", "value", a[e]);
						if (!h.data("binded")) {
							h.data("binded", true);
							h.on("slide", function(a, c) {
								var d = {};
								d[e] = c.value;
								b.updateCssObject(e, c.value);
								b.updateCssPreview();
							});
						}
					}
					break;

				case "font-style":
				case "text-decoration":
					var h = $("css_" + e);
					if (h) {
						if (a[e]) h.value = a[e];
						if (!h.binded) {
							h.binded = true;
							h.observe("change", function() {
								var a = {};
								a[e] = h.value;
								this.updateCssObject(e, h.value);
								this.updateCssPreview();
							}.bind(this));
						}
					}
					break;

				case "background":
					var i = jQuery("#css_background-transparency"),
						j = jQuery("#css_background-color");
					if (i.length && j.length) {
						if (!a["background-color"]) {
							j.val("");
							i.slider("option", "value", 100);
						} else if (0 === a["background-color"].indexOf("rgb")) {
							var k = a["background-color"].replace(/[rgba\(\)]/g, ""),
								l = k.split(","),
								m = f(parseInt(l[0]), parseInt(l[1]), parseInt(l[2]));
							j.val(m);
							if (l[3]) i.slider("option", "value", 100 * parseFloat(l[3]));
							else i.slider("option", "value", 100);
						} else {
							j.val(a["background-color"]);
							i.slider("option", "value", 100);
						}
						if (!j.binded) {
							j.binded = true;
							j.on("change", function() {
								d(j, i.slider("value"));
							});
						}
						$("css_background-color").color && $("css_background-color").color.importColor();
						if (!i.data("binded")) {
							i.data("binded", true);
							i.on("slide", function(a, b) {
								d(j, b.value);
							});
						}
					}
					break;

				case "color":
				case "border-color":
					var h = $("css_" + e);
					if (h) {
						if (a[e])
							if (0 === a[e].indexOf("rgb")) {
								var k = a[e].replace(/[rgba\(\)]/g, ""),
									l = k.split(","),
									m = f(parseInt(l[0]), parseInt(l[1]), parseInt(l[2]));
								h.value = m.toUpperCase();
							} else h.value = a[e];
						if (!h.binded) {
							h.binded = true;
							h.observe("change", function() {
								var a = {};
								a[e] = "#" + this.cleanColor(h.value);
								this.updateCssObject(e, a[e]);
								this.updateCssPreview();
							}.bind(this));
						}
						h.color && h.color.importColor();
					}
					break;

				case "css":
					var h = $("css_css");
					if (h) {
						var n = this.getCssFromObject(a, false);
						if (CodeMirror)
							if (!h.cm) {
								h.value = n;
								this.cssCM = CodeMirror.fromTextArea(h, {
									mode: "css"
								});
								this.cssCM.on("blur", function(a) {
									var b = a.getValue(),
										c = this.getStyleFromCss(b);
									if ("normal" == this.cssState) this.cssObject = c;
									else this.cssHover = c;
								}.bind(this));
								h.cm = this.cssCM;
							}
					}
					break;

				default:
					var h = $("css_" + e);
					if (h) {
						h.value = a[e];
						if (!h.binded) {
							h.binded = true;
							h.observe("change", function() {
								var a = {};
								a[e] = h.value;
								this.updateCssObject(e, h.value);
								this.updateCssPreview();
							}.bind(this));
						}
					}
			}
		}, this);
	},
	toggleAutotun: function() {
		var a = $("animation_control"),
			b = a.down("span");
		if (b)
			if (1 === arguments.length) {
				if (this.autorun) {
					var c = arguments[0];
					if (c) {
						b.addClassName("on");
						this.toggleAnimPreview(true);
					} else {
						b.removeClassName("on");
						this.toggleAnimPreview(false);
					}
				}
			} else if (this.selectedLayer)
				if (b.hasClassName("on")) {
					b.removeClassName("on");
					this.toggleAnimPreview(false);
					this.autorun = false;
				} else {
					b.addClassName("on");
					this.toggleAnimPreview(true);
					this.autorun = true;
				}
	},
	prepareAnimation: function() {
		this.animParams.each(function(a) {
			var b = $("layer_" + a);
			if (b) b.observe("change", function() {
				if (this.autorun) this.setInAnimation();
			}.bind(this));
		}, this);
		this.prepareAnimationTarget("animation_preview");
	},
	toggleAnimPreview: function(a) {
		var b = jQuery("#animation_preview");
		if (!b.length) return;
		if (a) {
			b.data("timer") && clearTimeout(b.data("timer"));
			b.removeClass("reset");
			this.setInAnimation();
		} else {
			b.data("timer") && clearTimeout(b.data("timer"));
			b.addClass("reset");
		}
	},
	getCustomAnimationParams: function() {
		var a = {};
		this.cusAnimParams.each(function(b) {
			var c = $$('input[name="anim-' + b + '"]')[0];
			if (c)
				if ("name" != b) a[b] = parseInt(c.value);
				else a[b] = c.value;
		});
		a["easing"] = $("anim_easing") ? $("anim_easing").value : "Linear.easeNone";
		a["speed"] = $("anim_speed") ? parseInt($("anim_speed").value) : null;
		if (isNaN(a["speed"])) a["speed"] = 500;
		a["speed"] = a["speed"] < 100 ? 100 : a["speed"];
		a["split"] = $("anim_split") ? $("anim_split").value : null;
		a["splitdelay"] = $("anim_splitdelay") ? parseInt($("anim_splitdelay").value) / 100 : null;
		if (isNaN(a["splitdelay"])) a["splitdelay"] = 10 / 100;
		return a;
	},
	setInAnimation: function() {
		var aTget = jQuery("#layer_animation_preview");
		var CmrContent = jQuery("#CmrContent");
		var a = jQuery("#animation_preview"),
			thisDelay = $('layer_time_delay_transitions').value || '1500',
			dataTime = $('layer_time_transitions').value || '500',
			thisFxIn = $("layer_data_fxin").value,
			thisFadeIn = $("layer_data_fadein").value,
			thisEaseIn = $("layer_data_easein").value;
		a.each(function() {
			var objW = a.outerWidth(),
				objH = a.outerHeight(),
				slideH = aTget.outerHeight(),
				slideW = aTget.outerWidth();
			var thisCss, thisAnim;
			var leftPercent = 36,
				topPercent = 40;
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
			eval("thisCss = {" + thisCss + ', visibility:"visible"}'), eval("thisAnim = {" + thisAnim + "}"), "0" != thisFadeIn ? a.animate({
				opacity: 0
			}, 0) : a.animate({
				opacity: 1
			}, 0), a.css(thisCss), a.stop(!0, !0).delay(thisDelay).animate(thisAnim, dataTime, thisEaseIn)
		});
		setTimeout(function() {
			this.setOutAnimation();
		}.bind(this), dataTime);
	},
	setOutAnimation: function() {
		var aTget = jQuery("#layer_animation_preview");
		var a = jQuery("#animation_preview"),
			thisDelay = $('layer_time_delay_transitions').value || '1500',
			dataTime = $('layer_time_transitions').value || '500',
			thisFxOut = $("layer_data_fxout").value,
			thisFadeOut = $("layer_data_fadeout").value,
			thisEaseOut = $("layer_data_easeout").value;
		a.each(function() {
			var objW = a.outerWidth(),
				objH = a.outerHeight(),
				slideH = aTget.outerHeight(),
				slideW = aTget.outerWidth(),
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
			var index = a.index(),
				leng = a.length;
		})
		setTimeout(function() {
			this.setInAnimation();
		}.bind(this), dataTime);
	},
	collectContainer: function() {
		this.container = $("divLayers");
		var width_sliders = $('preview_width').value;
		var height_sliders = $('preview_height').value;
		this.container.style.width = width_sliders + "px";
		this.container.style.height = height_sliders + "px";
		Event.observe(this.container, "click", function(a) {
			var b = Event.element(a);
			if (b == this.container) this.selectLayer();
		}.bind(this));
		this.list = $("listLayers");
		this.list.sort = "depth";
	},
	collectBtns: function() {
		this.deleteBtn = $("deleteLayerBtn") || null;
		this.dupLayerBtn = $("dupLayerBtn") || null;
		this.editLayerBtn = $("editLayerBtn") || null;
		this.cInAnimation = $("cInAnimation") || null;
		this.cNewInAnimation = $("cNewInAnimation") || null;
		this.cOutAnimation = $("cOutAnimation") || null;
		this.cNewOutAnimation = $("cNewOutAnimation") || null;
		this.editStyleBtn = $("editStyleBtn") || null;
	},
	collectParamsElement: function() {
		this.layerParams.each(function(a) {
			var b = $("layer_" + a);
			if (b) {
				if ("style" == a)
					for (var c = 0; c < b.options.length; c++) this.styles.set(b.options[c].value, b.options[c].innerHTML);
				this.layerParamsElm[a] = b;
				if ("TABLE" === b.tagName) b.select("a").each(function(a) {
					Event.observe(a, "click", function(a) {
						a.stop();
						if (b.hasClassName("disabled")) return false;
						var c = Event.findElement(a, "a");
						b.select("a").each(function(a) {
							a.removeClassName("selected");
						});
						c.addClassName("selected");
						this.selectedLayer.params.align_hor = c.readAttribute("data-hor");
						this.selectedLayer.params.align_vert = c.readAttribute("data-ver");
						this.selectedLayer.params.align = c.readAttribute("data-id");
						this.checkFullWidthVideo(this.selectedLayer.params);
						this.updateAlign(this.selectedLayer);
					}.bind(this));
				}.bind(this));
				else b.observe("change", function(b) {
					var c = Event.element(b);
					if (this.selectedLayer) {
						var checkclass = this.selectedLayer.hasClassName('hide');
						var d = this.selectedLayer.params[a];
						this.selectedLayer.params[a] = c.value;
						this.updateListItem(this.selectedLayer.params);
						this.selectedLayer.style.width = this.selectedLayer.params.width + '%';
						this.selectedLayer.style.height = this.selectedLayer.params.height + '%';
						this.selectedLayer.style.top = this.selectedLayer.params.top ? this.selectedLayer.params.top + "px" : '';
						this.selectedLayer.style.left = this.selectedLayer.params.left ? this.selectedLayer.params.left + "px" : '';
						this.selectedLayer.style.backgroundColor = "#" + this.selectedLayer.params.bg_color;
						this.selectedLayer.style.color = "#" + this.selectedLayer.params.color;
						this.selectedLayer.style.fontFamily = this.selectedLayer.params.font_family;
						this.selectedLayer.style.fontSize = this.selectedLayer.params.font_size + "px";
						this.selectedLayer.style.fontStyle = this.selectedLayer.params.textItalic;
						this.selectedLayer.style.fontWeight = this.selectedLayer.params.textBold;
						this.selectedLayer.style.textAlign = this.selectedLayer.params.text_align;
						this.selectedLayer.style.textDecoration = this.selectedLayer.params.textUnderline;

						//if ("text" === a || 'title_link' === a) {
						if ("text" === a) {
							if ("text" === this.selectedLayer.params.type)
								this.selectedLayer.innerHTML = c.value;
							else if ("video" === this.selectedLayer.params.type) {
								this.selectedLayer.params.video_data.title = c.value.escapeHTML();
								var e = this.selectedLayer.down("span");
								if (e) e.update(c.value.escapeHTML());
							}
						} else if ("left" === a || "top" === a) this.updateAlign(this.selectedLayer);
						else if ("style" === a) {
							var f = c.options[c.selectedIndex].innerHTML;
							this.selectedLayer.params[a] = f;
							this.selectedLayer.params["style_id"] = c.value;
							this.selectedLayer.removeClassName(d);
							this.selectedLayer.addClassName(f);
						} else if ("width" === a) this.setScale(true, false);
						else if ("height" === a) this.setScale(false, false);
						this.setSttClasses(checkclass);
					}
				}.bind(this));
			}
		}.bind(this));
	},
	setSttClasses: function(checkclass) {
		if (checkclass) {
			this.selectedLayer.params["classes"] = 'hide';
		} else {
			this.selectedLayer.params["classes"] = '';
		}
	},
	setSttAllClasses: function(a, b) {
		var idn = this.getLayer(a)
		var gn = idn.hasClassName('hide');
		if (b) {
			idn.params['classes'] = 'hide';
		} else {
			idn.params['classes'] = '';
		}
	},
	renderLayerHtml: function(a) {
		if (a.classes === "hide") {
			var b = new Element("div", {
				id: "sliders_layer_" + a.serial,
				"class": "sliders_layer hide tp-caption "
			});
		} else {
			var b = new Element("div", {
				id: "sliders_layer_" + a.serial,
				"class": "sliders_layer tp-caption "
			});
		}
		b.setStyle({
			zIndex: Number(a.order),
			position: "absolute"
		});
		switch (a.type) {
			case "image":
				var c = 0 === a.image_url.indexOf("http") ? a.image_url : this.mediaUrl + a.image_url;
				var d = new Element("img", {
					src: c
				});
				a.width && b.setStyle({
					width: a.width + '%'
				});
				a.height && b.setStyle({
					height: a.height + '%'
				});

				d.setStyle({
					width: 100 + '%',
					height: 100 + '%'
				});
				a.bg_color && d.setStyle({
					backgroundColor: "transparent"
				});

				b.insert(d);
				b.addClassName("layer-img");
				break;

			case "video":
				var e = this.renderVideoLayerHtml(a);
				a.video_width && b.setStyle({
					width: a.video_width + '%'
				});
				a.video_height && b.setStyle({
					height: a.video_height + '%'
				});
				b.insert(e);
				b.addClassName("layer-video");
				break;

			case "text":
			default:
				b.innerHTML = a.text;
				b.style.backgroundColor = "#" + a.bg_color;
				b.style.width = a.width + '%';
				b.style.height = a.height + '%';
				b.style.top = a.top;
				b.style.left = a.left;
				b.style.color = "#" + a.color;
				b.style.textAlign = a.text_align;
				b.style.textDecoration = a.textUnderline;
				b.style.fontStyle = a.textItalic;
				b.style.fontWeight = a.textBold;
				b.style.fontFamily = a.font_family;
				b.style.fontSize = a.font_size + 'px';
				break;
		}
		return b;
	},
	toggleEditStyle: function(a) {
		if (this.editStyleBtn)
			if (a)
				if (this.selectedLayer && "video" != this.selectedLayer.params.type)
					enableElement(this.editStyleBtn);
				else disableElement(this.editStyleBtn);
			else disableElement(this.editStyleBtn);
	},
	toogleDelete: function(a) {
		if (this.deleteBtn && this.dupLayerBtn && this.editLayerBtn)
			if (a) {
				enableElement(this.deleteBtn);
				enableElement(this.dupLayerBtn);
				if (this.selectedLayer && "text" != this.selectedLayer.params.type) enableElement(this.editLayerBtn);
				else disableElement(this.editLayerBtn);
			} else {
				disableElement(this.deleteBtn);
				disableElement(this.dupLayerBtn);
				disableElement(this.editLayerBtn);
			}
	},
	updateContainer: function() {
		var a = $("background_type");
		if (a) switch (a.value) {
			case "image":
				var b = "image" === a.value ? $("data_src") : $("sliders_bg_color");
				if (b && b.value) {
					var c = 0 === b.value.indexOf("http") ? b.value : this.mediaUrl + b.value;
					this.container.setStyle({
						backgroundImage: "url(" + c + ")"
					});
				} else this.container.setStyle({});
				break;

			case "color":
				this.container.removeClassName("transparent");
				var g = $("sliders_bg_color");
				if (g) this.container.setStyle({
					backgroundImage: "",
					backgroundColor: "#" + this.cleanColor(g.value)
				});
		}
		this.updateContainerOpts();
	},
	getCheckedLoop: function(a, b) {
		if (b) {
			return a.value;
		} else {
			return '';
		}
	},
	getCheckedControls: function(a, b) {
		if (b) {
			return a;
		} else {
			return '';
		}
	},
	getCheckedAutoPlay: function(a, b) {
		if (b) {
			return a;
		} else {
			return '';
		}
	},
	getCheckedMuted: function(a, b) {
		if (b) {
			return a;
		} else {
			return '';
		}
	},
	onSelectHtml5Video: function(a, b) {
		Windows.close(b);
	},
	cleanColor: function(a) {
		return 0 === a.indexOf("#") ? a.replace("#", "") : a;
	},
	updateContainerOpts: function() {
		this.container.setStyle({
			backgroundSize: "normal",
			backgroundRepeat: "no-repeat",
			backgroundPosition: "center"
		});
	},
	updateList: function() {
		var a = jQuery("#timeline");
		if (!a.length) return;
		a.find("span.min").html("0ms");
		a.find("span.max").html(this.time_load + "ms");
		var b = this.time_load,
			c = 0;
		this.slider = a.slider({
			range: true,
			min: c,
			max: b,
			values: [0, 0]
		});
	},
	addLayerText: function() {
		var a = {
			text: "Text " + (this.index + 1),
			type: "text"
		};
		this.addLayer(a);
	},
	addLayer: function(a) {
		var b = this.container.getDimensions();
		if (!b.width && !b.height) {
			setTimeout(function() {
				this.addLayer(a);
			}.bind(this), 500);
			return;
		}
		if (void 0 == a.order) a.order = this.index + 1;
		if (void 0 == a.left) a.left = 10;
		if (void 0 == a.top) a.top = 10;
		if (void 0 == a.text_align) a.text_align = $("layer_text_align").value;
		if (void 0 == a.textBold) a.textBold = $("layer_textBold").value;
		if (void 0 == a.textItalic) a.textItalic = $("layer_textItalic").value;
		if (void 0 == a.textUnderline) a.textUnderline = $("layer_textUnderline").value;
		if (void 0 == a.font_family) a.font_family = "";
		if (void 0 == a.font_size) a.font_size = "";
		if (void 0 == a.width) a.width = "";
		if (void 0 == a.min_width) a.min_width = "";
		if (void 0 == a.height) a.height = "";
		if (void 0 == a.min_height) a.min_height = "";
		if (a.type === 'text') {
			if (void 0 == a.bg_color) a.bg_color = "transparent";
		} else {
			if (void 0 == a.bg_color) a.bg_color = "";
		}

		if (void 0 == a.color) a.color = "EEEEEE";
		if (void 0 == a.data_easein) a.data_easein = $("layer_data_easein").value;
		if (void 0 == a.data_easeout) a.data_easeout = $("layer_data_easeout").value;
		if (void 0 == a.data_fxin) a.data_fxin = $("layer_data_fxin").value;
		if (void 0 == a.data_fxout) a.data_fxout = $("layer_data_fxout").value;
		if (void 0 == a.data_fadein) a.data_fadein = $("layer_data_fadein").value;
		if (void 0 == a.data_fadeout) a.data_fadeout = $("layer_data_fadeout").value;
		if (void 0 == a.speed) a.speed = 500;
		if (void 0 == a.class) a.class = "";
		if (void 0 == a.alt_image) a.alt_image = "";
		if (void 0 == a.title_image_link) a.title_image_link = "";
		if (void 0 == a.link) a.link = "";
		if (void 0 == a.target_link) a.target_link = "_blank";
		if (void 0 == a.time_transitions) {
			var c_time = 500 * a.order;
			a.time_transitions = c_time > this.delay ? this.delay : c_time;
		}
		if (void 0 == a.time_delay_transitions) {
			var c = 1000 * a.order;
			a.time_delay_transitions = c > this.delay ? this.delay : c;
		}
		if (void 0 == a.id) a.id = "";
		if (void 0 == a.classes) a.classes = "";
		if (void 0 == a.alt) a.alt = "";
		a.serial = this.index + 1;
		a.top = Math.round(a.top);
		a.left = Math.round(a.left);
		a.sort = null;
		this.layers[a.serial] = a;
		this.checkFullWidthVideo(a);
		var d = this.renderLayerHtml(a);
		d.params = a;
		this.container.insert(d);
		this.bindLayerEvent(d);
		this.updateAlign(d);
		this.updateLayerHtmlCorners(a);
		this.addListItem(a);
		this.updateHideItemLayer();
		this.index++;
		this.count++;
	},
	checkFullWidthVideo: function(a) {
		if ("video" == a.type && a.video_data && true === a.video_data.fullwidth) {
			a.top = 0;
			a.left = 0;
			a.align_hor = "left";
			a.align_vert = "top";
			a.video_height = 100;
			a.video_width = 100;
			return a;
		}
	},
	selectLayer: function() {
		var a;
		if (1 === arguments.length) a = $(arguments[0]);
		if (a) {
			this.selectedLayer = a;
			this.container.select(".sliders_layer").each(function(a) {
				a.removeClassName("selected");
			});
			a.addClassName("selected");
			this.list.select(".item").each(function(a) {
				a.removeClassName("selected");
			});
			var b = this.list.down("#item_" + a.params.serial);

			if (b) b.addClassName("selected");
			this.layerParams.each(function(b) {
				this.layerParamsElm[b].checked = a.params[b];
				this.layerParamsElm[b].value = a.params[b];
				switch (a.params.type) {
					case "video":
						switch (b) {
							case "bg_color":
							case "color":
							case "text":
							case "text_align":
							case "textBold":
							case "textItalic":
							case "textUnderline":
							case "font_family":
							case "font_size":
							case "alt_image":
							case "title_image_link":
							case "target_link":
							case "link":
							case "min_width":
							case "min_height":
							case "width":
							case "height":
								disableElement(this.layerParamsElm[b]);
								break;

							default:
								enableElement(this.layerParamsElm[b]);
						}
						break;

					case "image":
						switch (b) {
							case "bg_color":
							case "color":
							case "text":
							case "text_align":
							case "textBold":
							case "textItalic":
							case "textUnderline":
							case "font_family":
							case "font_size":
								disableElement(this.layerParamsElm[b]);
								break;

							default:
								enableElement(this.layerParamsElm[b]);
						}
						break;
					default:
						switch (b) {
							case "alt_image":
							case "title_image_link":
							case "target_link":
							case "link":
								disableElement(this.layerParamsElm[b]);
								break;

							default:
								$("layer_bg_color").style.backgroundColor = "#" + a.params.bg_color;
								$("layer_color").style.backgroundColor = "#" + a.params.color;
								enableElement(this.layerParamsElm[b]);
						}
						break;
				}
			}.bind(this));
			this.toggleAutotun(true);
			this.toogleDelete(true);
		} else {
			this.selectedLayer = null;
			this.toggleAutotun(false);
			this.toogleDelete(false);
			//this.layerParams.disabled = true;
			this.layerParams.each(function(a) {
				//console.log(this.layerParamsElm);
				//this.layerParamsElm[a].disabled = true;
			}.bind(this));
			this.container.select(".sliders_layer").each(function(a) {
				a.removeClassName("selected");
			});
			this.list.select(".item").each(function(a) {
				a.removeClassName("selected");
			});
			if (this.slider) this.slider.slider("disable");

		}
	},
	addLayerImage: function(a, b) {
		if (b) {
			var c = this.selectedLayer.params;
			c.image_url = a;
			this.updateLayerHtml(c);
		} else {
			var c = {
				style: "",
				text: "Image " + (this.index + 1),
				type: "image",
				image_url: a
			};
			this.addLayer(c);
		}
	},
	addLayerVideo: function(a) {
		if (editForm && editForm.validate()) {
			var b = this.videoData || {};
			this.videoParams.each(function(a) {
				var c = $("video_" + a);
				if (c && "checkbox" == c.readAttribute("type"))
					b[a] = c.checked;
				else if (c)
					b[a] = c.value.trim();
			});
			b.title = $("video_title").value;
			b.service_video = $("service_video").value;
			var c = $("video_serial"),
				d = {};
			if (c && c.value) {
				d = this.layers[c.value];
				Object.extend(d.video_data, b);
				d.service_video = d.video_data.service_video;
				d.video_width = d.video_data.width;
				d.video_height = d.video_data.height;
				var http = window.location.origin + "/media/";
				switch (d.service_video) {
					case "youtube":
					case "vimeo":
						d.video_id = d.video_data.id;
						d.video_title = d.video_data.title;
						d.video_image_url = d.video_data.thumb_medium.url;
						break;

					case "html5":
						b.urlPoster = $("video_poster").value;
						b.urlMp4 = $("video_html5_mp4").value;
						b.urlWebm = $("video_html5_webm").value;
						b.urlOgg = $("video_html5_ogg").value;
						if (!b.urlMp4 && !b.urlOgg && !b.urlWebm) {
							alert(Translator.translate("No video source found!"));
							return;
						}
						b.title = b.title || Translator.translate("HTML5 Video");

						d.video_image_url = http + b.urlPoster;
				}
				Object.extend(d.video_data, b);
				d.video_args = d.video_data.args;
				d.text = d.video_data.title;
				this.checkFullWidthVideo(d);
				this.updateLayerHtml(d);
				this.updateListItem(d);
			} else {
				d.type = "video";
				d.style = "";
				d.service_video = b.service_video;
				switch (d.service_video) {
					case "youtube":
					case "vimeo":
						d.video_id = b.id;
						d.video_title = b.title;
						d.video_image_url = b.thumb_medium.url;
						break;

					case "html5":
						b.urlPoster = $("video_poster").value;
						b.urlMp4 = $("video_html5_mp4").value;
						b.urlWebm = $("video_html5_webm").value;
						b.urlOgg = $("video_html5_ogg").value;
						if (!b.urlMp4 && !b.urlOgg && !b.urlWebm) {
							alert(Translator.translate("No video source found!"));
							return;
						}
						b.title = b.title || Translator.translate("HTML5 Video");
						d.video_image_url = b.urlPoster;
				}
				d.video_width = b.width;
				d.video_height = b.height;
				d.video_data = b;
				d.video_args = b.args;
				d.text = b.title;
				this.addLayer(d);
			}
			this.videoData = null;
			Windows.close(a);
		}
	},
	onChangeVideoFullWidth: function(a) {
		var b = a.checked;
		"width|height".split("|").each(function(a) {
			var c = $("video_" + a);
			if (c)
				if (b) disableElement(c);
				else enableElement(c);
		});
	},
	assignVideoForm: function(a) {
		if (a) {
			var b = this.layers[a].video_data;
			$("service_video").value = b.service_video;
			if (fireEvent) fireEvent($("service_video"), "change");
			switch (b.service_video) {
				case "youtube":
				case "vimeo":
					this.toggleVideoForm(true);
					break;

				case "html5":
					$("video_poster").value = b.urlPoster;
					$("video_html5_mp4").value = b.urlMp4;
					$("video_html5_webm").value = b.urlWebm;
					$("video_html5_ogg").value = b.urlOgg;
			}
			this.videoParams.each(function(a) {
				var c = $("video_" + a);
				if (c) {
					c.value = b[a];
					c.checked = b[a];
					if (fireEvent) fireEvent(c, "change");
				}
			}.bind(this));
			$("src_video").value = b.id;
			this.updateVideoView(b);
		}
	},
	updateVideoView: function(a) {
		var b = $("video_thumb_wrapper");
		var c = $("video_title");
		if (a) {
			if (a.title) c.value = a.title;
			b.update("");
			switch (a.service_video) {
				case "youtube":
				case "vimeo":
					var d = a.thumb_medium;
					var e = new Element("img", {
						src: d.url,
						width: d.width + "px",
						height: d.height + "px"
					});
					e.setStyle({
						border: "1px solid #000"
					});
					b.insert(e);
					break;

				case "html5":
					if (!a.urlPoster) return;
					var d = 0 === a.urlPoster.indexOf("http") ? a.urlPoster : this.mediaUrl + a.urlPoster;
					var e = new Element("img", {
						src: d,
						width: 280 + "px"
					});
					e.setStyle({
						border: "1px solid #000"
					});
					b.insert(e);
			}
		} else {
			b.update("");
			c.value = "";
		}
	},
	onChangeVideoType: function(a) {
		if ("html5" === a.value) this.toggleVideoForm(true);
		else this.toggleVideoForm(false);
		$("src_video").value = "";
		this.updateVideoView(null);
	},
	toggleVideoForm: function(a) {
		this.videoParams.each(function(b) {
			var c = $("video_" + b);
			if (c) {
				if (a) enableElement(c);
				else disableElement(c);
				if (fireEvent) fireEvent(c, "change");
			}
		});
	},
	updateVieoControl: function(a) {
		switch (a.service_video) {
			case "youtube":
				$("video_args").value = "hd=1&wmode=opaque&controls=1&showinfo=0;rel=0;";
				break;

			case "vimeo":
				$("video_args").value = "title=0&byline=0&portrait=0&api=1";
				break;
		}
		$("videoLoading").hide();
		this.toggleVideoForm(true);
	},
	onYoutubeCallback: function(a) {
		var b = a.entry;
		var c = {};
		c.id = this.lastVideoId;
		c.service_video = "youtube";
		c.title = b.title.$t;
		c.author = b.author[0].name.$t;
		c.link = b.link[0].href;
		var d = b.media$group.media$thumbnail;
		c.thumb_small = {
			url: d[0].url,
			width: d[0].width,
			height: d[0].height
		};
		c.thumb_medium = {
			url: d[1].url,
			width: d[1].width,
			height: d[1].height
		};
		c.thumb_big = {
			url: d[2].url,
			width: d[2].width,
			height: d[2].height
		};
		this.videoData = c;
		this.updateVideoView(c);
		this.updateVieoControl(c);
	},
	onVimeoCallback: function(a) {
		a = a[0];
		var b = {};
		b.service_video = "vimeo";
		b.id = a.id;
		b.title = a.title;
		b.link = a.url;
		b.author = a.user_name;
		b.thumb_large = {
			url: a.thumbnail_large,
			width: 640,
			height: 360
		};
		b.thumb_medium = {
			url: a.thumbnail_medium,
			width: 200,
			height: 150
		};
		b.thumb_small = {
			url: a.thumbnail_small,
			width: 100,
			height: 75
		};
		this.videoData = b;
		this.updateVideoView(b);
		this.updateVieoControl(b);
	},
	searchVideo: function() {
		var a = $("service_video").value;
		var b = $("src_video").value.trim();
		var c = this.getVideoId(a, b);
		if (c) {
			this.lastVideoId = c;
			var d = $$("head")[0];
			var e = new Element("script", {
				type: "text/javascript"
			});
			switch (a) {
				case "youtube":
					var f = "https://gdata.youtube.com/feeds/api/videos/" + c + "?v=2&alt=jsonc&callback=CmrSl.onYoutubeCallback";
					e.src = f;
					d.appendChild(e);
					break;

				case "vimeo":
					var f = "http://vimeo.com/api/v2/video/" + c + ".json?callback=CmrSl.onVimeoCallback";
					e.src = f;
					d.appendChild(e);
			}
			setTimeout(function() {
				$("videoLoading") && $("videoLoading").hide();
			}, 5e3);
			$("videoLoading").show();
		}
	},
	getVideoId: function(a, b) {
		switch (a) {
			case "youtube":
				var c = b.split("v=")[1];
				if (c) {
					var d = c.indexOf("&");
					if (d != -1) c = c.substring(0, d);
				} else c = b;
				return c;
				break;

			case "vimeo":
				var c = b.replace(/[^0-9]+/g, "");
				return c;
		}
		return null;
	},
	duplicateLayer: function() {
		if (this.selectedLayer) {
			var a = Object.clone(this.selectedLayer.params);
			a.left += 20;
			a.top += 20;
			a.serial = void 0;
			//a.time = void 0;
			a.order = this.count + 1;
			this.addLayer(a);
		}
	},
	editLayer: function() {
		if (this.selectedLayer) switch (this.selectedLayer.params.type) {
			case "image":
				var a = $("addLayerImageUrl");
				if (a) {
					var b = a.value;
					b = b + "onInsertCallbackParams/" + this.selectedLayer.params.serial;
					_MediabrowserUtility.openDialog(b, "editLayerImageWindow", null, null, Translator.translate("Update Image"));
				}
				break;

			case "video":
				var c = $("addLayerVideoUrl");
				if (c) {
					var b = c.value;
					b += "serial/" + this.selectedLayer.params.serial;
					_MediabrowserUtility.openDialog(b, "editLayerVideoWindow", null, null, Translator.translate("Update Video"));
				}
		}
	},
	deleteLayer: function() {
		if (this.selectedLayer) {
			delete this.layers[this.selectedLayer.params.serial];
			this.selectedLayer.remove();
			var a = this.getItem(this.selectedLayer.params.serial);
			if (a) a.remove();
			delete this.selectedLayer;
			this.count--;
			this.selectLayer();
			this.updateHideItemLayer();
		}
	},
	deleteAllLayers: function() {
		if (confirm(Translator.translate("Do you really want to delete all the layers?"))) {
			this.deleteLayer();
			this.container.update("");
			this.list.update("");
			this.layers = {};
			this.count = 0;
			this.selectLayer();
		}
	},
	save: function(a) {
		if (this.form && this.form.validate()) {
			var b = this.form.validator.form;
			var c = b.action;
			var d = b.serialize(true);
			if (d["data_transitions_fx[]"]) {
				d["data_transitions_fx"] = d["data_transitions_fx[]"].join(",");
				delete d["transitions[]"];
			}
			d.layers = JSON.stringify(this.layers);
			new Ajax.Request(c, {
				method: "post",
				parameters: d,
				onSuccess: function(b) {
					if (a) window.location.reload();
					else if (0 === b.responseText.indexOf("http://")) window.location.href = b.responseText;
				}
			});
		}
	},
	getLayer: function(a) {
		return this.container.down("#sliders_layer_" + a);
	},
	getItem: function(a) {
		return this.list.down("#item_" + a);
	},
	sortLayerItem: function(a, b) {
		this.list.sort = b;
		a = $(a);
		a.up().select("button").invoke("addClassName", "normal");
		a.removeClassName("normal");
		var c = [];
		for (var d in this.layers) {
			var e = this.layers[d];
			c.push(e);
		}
		switch (b) {
			case "depth":
				c.sort(function(a, b) {
					return a.order - b.order;
				});
		}
		this.list.update("");
		c.each(function(a) {
			this.addListItem(a);
		}, this);
		this.selectLayer(this.selectedLayer);
		this.updateHideItemLayer();
	},
	changeTimeLayerWithNumberOrder: function(a) {
		$('layer_time_transitions').value = 500 * a.order;
		a.time_transitions = 500 * a.order;
		$('layer_time_delay_transitions').value = 1000 * a.order;
		a.time_delay_transitions = 1000 * a.order;
	},
	updateHideItemLayer: function() {
		for (var a in this.layers)
			if (false == this.isLayerVisible(a)) this.hideLayer(a);
			else this.showLayer(a);
	},
	updateListItem: function(a) {
		var b = this.list.down("#item_" + a.serial);
		if (b) b.down(".name").innerHTML = this.getListItemName(a);
	},
	getListItemName: function(a) {
		var b = a.text.stripTags().escapeHTML();
		switch (a.service_video) {
			case "youtube":
				return "Youtube: " + b;
				break;

			case "vimeo":
				return "Vimeo: " + b;
				break;

			case "html5":
				return "Video: " + b;
				break;

			default:
				return b;
		}
	},
	addListItem: function(a) {
		var b = new Element("div", {
			"class": "item",
			id: "item_" + a.serial,
			title: Translator.translate("Drag to change layer depth")
		});
		var c = new Element("div", {
			"class": "name"
		});
		c.innerHTML = this.getListItemName(a);
		var d = new Element("input", {
			type: "text",
			readonly: "readonly",
			"class": "input-text order validate-number",
			title: Translator.translate("Layer Depth")
		});
		d.value = a.order;

		if ("depth" === a.sort) e.addClassName("fade");
		var f = new Element("span", {
			"class": "arrow"
		});
		f.insert("<i class='fa fa-arrows-v'></i>");
		var g = new Element("span", {
			"class": "eye",
			title: Translator.translate("Click to Show / Hide layer")
		});
		g.insert('<i class="fa fa-eye"></i>');
		Event.observe(g, "click", function(a) {
			var b = Event.findElement(a, "div.item");
			var c = b.params.serial;
			if (this.isLayerVisible(c)) this.hideLayer(c);
			else this.showLayer(c);
		}.bind(this));
		var h = new Element("div", {
			"class": "right"
		});
		h.insert(d);
		h.insert(g);
		b.insert(f);
		b.insert(c);
		b.insert(h);
		b.params = a;
		this.list.insert(b);
		this.bindListItemEvent(b);
		this.bindListEvent(this.list);
	},
	setHideAll: function() {
		var a = $("button_sort_visibility");
		if (a.hasClassName("e-disabled")) {
			a.removeClassName("e-disabled");
			this.showAllLayers();
		} else {
			a.addClassName("e-disabled");
			this.hideAllLayers();
		}
	},
	isLayerVisible: function(a) {
		var b = this.getLayer(a);
		var c = !b.hasClassName("hide");
		return c;
	},
	isAllLayersHidden: function() {
		for (var a in this.layers)
			if (true == this.isLayerVisible(a)) return false;
		return true;
	},
	getLayerHidden: function() {
		var a = [];
		for (var b in this.layers)
			if (false == this.isLayerVisible(b)) a.push(b);
		return a;
	},
	hideLayer: function(a, b) {
		var c = this.getLayer(a);
		c.addClassName("hide");
		this.setSortboxItemHidden(a);
		if (true != b)
			if (this.isAllLayersHidden()) $("button_sort_visibility").addClassName("e-disabled");
	},
	hideAllLayers: function() {
		for (var a in this.layers) {
			this.hideLayer(a, true);
			this.setSttAllClasses(a, true);
		}
	},
	showLayer: function(a, b) {
		var c = this.getLayer(a);
		c.removeClassName("hide");
		this.setSortboxItemVisible(a);
		if (true != b) $("button_sort_visibility").removeClassName("e-disabled");
	},
	showAllLayers: function() {
		for (var a in this.layers) {
			this.showLayer(a, true);
			this.setSttAllClasses(a, false);
		}
	},
	setSortboxItemHidden: function(a) {
		var b = this.getItem(a);
		b.addClassName("hide");
	},
	setSortboxItemVisible: function(a) {
		var b = this.getItem(a);
		b.removeClassName("hide");
	},
	bindListItemEvent: function(a) {
		Event.observe(a, "click", function(a) {
			var b = Event.findElement(a, "div.item");
			var c = this.getLayer(b.params.serial);
			if (c) this.selectLayer(c);
		}.bind(this));
	},
	bindListEvent: function(a) {
		Sortable.create(a, {
			tag: "div",
			onUpdate: function() {
				this.reorderLayers();
			}.bind(this)
		});
	},
	reorderLayers: function() {
		switch (this.list.sort) {
			case "depth":
				var a = 1;
				this.list.select(".item").each(function(b) {
					var c = this.getLayer(b.params.serial);
					if (c) {
						c.params.order = a++;
						this.updateLayerHtml(c.params);
						this.updateListHtml(c.params);
						this.changeTimeLayerWithNumberOrder(c.params);
					}
				}, this);
				break;
		}
	},
	bindLayerEvent: function(a) {
		if (a) {
			var b = this.container.getDimensions();
			var c = a.getDimensions();
			new Draggable(a, {
				snap: [1, 1, 1, 1],
				change: function(d) {
					var e = d.element;
					var f = e.positionedOffset();
					var g = f[1];
					var h = f[0];

					this.layerParamsElm.left.value = f[0];
					this.layerParamsElm.top.value = f[1];
					e.params.left = f[0];
					e.params.top = f[1];
				}.bind(this)
			});
			Event.observe(a, "mousedown", function(a) {
				var b = Event.findElement(a, "div.sliders_layer");
				if (this.selectedLayer != b) this.selectLayer(b);
			}.bind(this));
		}
	},
	updateAlign: function(a) {
		if (a) {
			var b = a.getDimensions();
			var c = this.container.getDimensions();
			var d = {};
			if (!c.height && !c.width) {
				setTimeout(function() {
					this.updateAlign(a);
				}.bind(this), 500);
				return;
			}
			switch (a.params.align_hor) {
				default:
				case "left":
					d.right = "auto";
					d.left = a.params.left + "px";
					break;

				case "right":
					d.left = "auto";
					d.right = a.params.left + "px";
					break;

				case "center":
					var e = Math.round((c.width - b.width) / 2) + parseInt(a.params.left);
					d.left = e + "px";
					d.right = "auto";
			}
			switch (a.params.align_vert) {
				default:
				case "top":
					d.bottom = "auto";
					d.top = a.params.top + "px";
					break;

				case "bottom":
					d.top = "auto";
					d.bottom = a.params.top + "px";
					break;

				case "middle":
					var f = Math.round((c.height - b.height) / 2) + parseInt(a.params.top);
					d.top = f + "px";
					d.bottom = "auto";
			}
			this.layerParamsElm.left.value = a.params.left;
			this.layerParamsElm.top.value = a.params.top;
			a.setStyle(d);
		}
	},
	updateListHtml: function(a) {
		var b = this.getItem(a.serial);
		if (b) {
			b.down("input.order").value = a.order;
		}
	},
	updateLayerHtml: function(a) {
		var b = this.getLayer(a.serial);
		if (b) {
			b.setStyle({
				zIndex: a.order
			});
			switch (a.type) {
				case "image":
					var c = 0 === a.image_url.indexOf("http") ? a.image_url : this.mediaUrl + a.image_url;
					b.down("img").src = c;
					setTimeout(function() {
						this.updateAlign(b);
					}.bind(this), 100);
					break;

				case "video":
					var d = this.renderVideoLayerHtml(a);
					b.update(d);
					setTimeout(function() {
						this.updateAlign(b);
					}.bind(this), 100);
			}
		}
	},
	renderVideoLayerHtml: function(a) {
		if (a) {
			var b = {
				height: 100 + '%',
				width: 100 + '%'
			};

			var c = new Element("div", {
				"class": "sliders_layer_video"
			});
			if (a.video_image_url)
				c.insert('<img src=' + a.video_image_url + ' alt="" style="width:100%;height:100%;" />');
			else {
				b.backgroundColor = "#000";
				b.height = 100 + '%';
				b.width = 100 + '%';
			}

			c.setStyle(b);
			switch (a.service_video) {
				case "html5":
					if (!a.video_image_url) {
						var d = new Element("span");
						d.update(a.text);
						c.update(d);
					}
			}
			return c;
		}
		return null;
	},
	updateLayerHtmlScale: function(a) {
		var b = this.getLayer(a.serial),
			c = b.down("img");
		if (c) c.setStyle({
			width: a.width + '%',
			height: a.height + '%'
		});
	},
	updateLayerHtmlVisibility: function(a) {
		var b = this.getLayer(a.serial),
			c = b.down("img");
		if (c) c.setStyle({
			width: a.width + '%',
			height: a.height + '%'
		});
	},
	updateLayerHtmlCorners: function(a) {
		var b = this.getLayer(a.serial),
			c = b.offsetHeight,
			d = b.getStyle("backgroundColor");
		if (b.down(".frontcorner")) b.down(".frontcorner").remove();
		if (b.down(".frontcornertop")) b.down(".frontcornertop").remove();
		switch (a.corner_left) {
			case "curved":
				if (!b.down(".frontcorner")) b.insert({
					bottom: '<div class="frontcorner"></div>'
				});
				break;

			case "reverced":
				if (!b.down(".frontcornertop")) b.insert({
					bottom: '<div class="frontcornertop"></div>'
				});
		}
		if (b.down(".backcorner")) b.down(".backcorner").remove();
		if (b.down(".backcornertop")) b.down(".backcornertop").remove();
		switch (a.corner_right) {
			case "curved":
				if (!b.down(".backcorner")) b.insert({
					bottom: '<div class="backcorner"></div>'
				});
				break;

			case "reverced":
				if (!b.down(".backcornertop")) b.insert({
					bottom: '<div class="backcornertop"></div>'
				});
		}
		b.down(".frontcorner") && b.down(".frontcorner").setStyle({
			borderWidth: c + "px",
			left: 0 - c + "px",
			borderRight: "0px solid transparent",
			borderTopColor: d
		});
		b.down(".frontcornertop") && b.down(".frontcornertop").setStyle({
			borderWidth: c + "px",
			left: 0 - c + "px",
			borderRight: "0px solid transparent",
			borderBottomColor: d
		});
		b.down(".backcorner") && b.down(".backcorner").setStyle({
			borderWidth: c + "px",
			right: 0 - c + "px",
			borderLeft: "0px solid transparent",
			borderBottomColor: d
		});
		b.down(".backcornertop") && b.down(".backcornertop").setStyle({
			borderWidth: c + "px",
			right: 0 - c + "px",
			borderLeft: "0px solid transparent",
			borderTopColor: d
		});
	},
	setScale: function(a, b) {
		if (this.selectedLayer && "image" === this.selectedLayer.params.type) {
			var c = this.selectedLayer.params,
				d = 0 === c.image_url.indexOf("http") ? c.image_url : this.mediaUrl + c.image_url,
				e = new Element("img", {
					src: d
				}),
				f = e.width,
				g = e.height;
			if (!b && a) {
				f = c.width;
				g = c.height;
			} else if (!b && !a) {
				g = c.height;
				f = c.width;
			}
			c.width = f;
			c.height = g;
			this.layerParamsElm["width"].value = f;
			this.layerParamsElm["height"].value = g;
			this.updateLayerHtmlScale(c);
			this.updateAlign(this.selectedLayer);
		}
	}
};