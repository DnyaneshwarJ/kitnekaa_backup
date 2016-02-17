	
jQuery.cookie = function(name, value, options) {
	if (typeof value != 'undefined') { // name and value given, set cookie
		options = options || {};
		if (value === null) {
			value = '';
			options.expires = -1;
		}
		var expires = '';
		if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
			var date;
			if (typeof options.expires == 'number') {
				date = new Date();
				date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
			} else {
				date = options.expires;
			}
			expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
		}
		// CAUTION: Needed to parenthesize options.path and options.domain
		// in the following expressions, otherwise they evaluate to undefined
		// in the packed version for some reason...
		var path = options.path ? '; path=' + (options.path) : '';
		var domain = options.domain ? '; domain=' + (options.domain) : '';
		var secure = options.secure ? '; secure' : '';
		document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
	} else { // only name given, get cookie
		var cookieValue = null;
		if (document.cookie && document.cookie != '') {
			var cookies = document.cookie.split(';');
			for (var i = 0; i < cookies.length; i++) {
				var cookie = jQuery.trim(cookies[i]);
				// Does this cookie string begin with the name we want?
				if (cookie.substring(0, name.length + 1) == (name + '=')) {
					cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
					break;
				}
			}
		}
		return cookieValue;

	}
};

function switchFontSize (ckname,val){
	var bd = document.getElementsByTagName('body');
	if (!bd || !bd.length) return;
	bd = bd[0];
	var oldclass = 'fs'+CurrentFontSize;
	switch (val) {
		case 'inc':
			if (CurrentFontSize+1 < 7) {
				CurrentFontSize++;
			}		
		break;
		case 'dec':
			if (CurrentFontSize-1 > 0) {
				CurrentFontSize--;
			}		
		break;
		case 'reset':
		default:
			CurrentFontSize = DefaultFontSize;			
	}
	var newclass = 'fs'+CurrentFontSize;
	bd.className = bd.className.replace(new RegExp('fs.?', 'g'), '');
	bd.className = trim(bd.className);
	bd.className += (bd.className?' ':'') + newclass;
	createCookie(ckname, CurrentFontSize, 365);
}
function trim(str, chars) {
	chars = chars || "\\s";
	str =   str.replace(new RegExp("^[" + chars + "]+", "g"), "");
	str =  str.replace(new RegExp("^[" + chars + "]+", "g"), "");
	return str;
}
function switchTool (ckname, val) {
	createCookie(ckname, val, 365);
	window.location.reload();
}

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}
function getCookie(c_name, defaultvalue){	//alert(document.cookie);
	var i,x,y,arrcookies=document.cookie.split(";");
	for (i=0;i<arrcookies.length;i++){
	  x=arrcookies[i].substr(0,arrcookies[i].indexOf("="));
	  y=arrcookies[i].substr(arrcookies[i].indexOf("=")+1);
	  x=x.replace(/^\s+|\s+$/g,"");
	  if (x==c_name){
		  return unescape(y);
	  }
	}
	return defaultvalue;
}

String.prototype.trim = function() { return this.replace(/^\s+|\s+$/g, ""); };

function menuFistLastItem () {
	if ((menu = $('nav')) && (children = menu.childElements()) && (children.length)) {
		children[0].addClassName ('first');
		children[children.length-1].addClassName ('last');
	}
}

//Add hover event for li - hack for IE6
function navMouseHover () {
	var lis = $$('#nav li');
	if (lis && lis.length) {
		lis.each (function(li){
			li.onMouseover = toggleMenu (li, 1);
			li.onMouseout = toggleMenu (li, 0);
		});
	}
}

toggleMenu = function (el, over) {
	  var iS = false;
    if (Element.childElements(el) && Element.childElements(el).length > 1) {
	    var uL = Element.childElements(el)[1];
			iS = true;
		}
    if (over) {
        Element.addClassName(el, 'over');
				Element.addClassName (el.down('a'), 'over');
        if(iS){ uL.addClassName('shown-sub')};
    }
    else {
        Element.removeClassName(el, 'over');
				Element.removeClassName (el.down('a'), 'over');
        if(iS){ uL.removeClassName('shown-sub')};
    }
}

function displayChildMenu(id){
	jQuery("#"+'child_menu'+id).css("display", "block");

	if ( jQuery("#"+'parent_menu'+id).attr("class").indexOf("active") < 0 ) 
		jQuery("#"+'parent_menu'+id).addClass("over");
}

function hideAllMenu(){
	menu = jQuery("[id*=child_menu]");
	
	jQuery.each(menu, function(){
		jQuery("#"+this.id).css("display", "none");
		jQuery("#parent_menu" + this.id.replace("child_menu", "") ).removeClass("over");
	});
}

function rollbackCurrentMenu(){
	hideAllMenu();
	jQuery("[rel=active_menu]").css("display", "block");
}

// Custom-Script
jQuery('document').ready(function($){

	var tabSection = $('.userlogin .tab-section');
	var tabBtn = tabSection.find('.tab-btn > li');
	var tabContent = tabSection.find('.myaccount-tab-content')
	tabBtn.first().addClass('active-tab');
	tabContent.first().show();
	tabBtn.click(function(){
		var selectTabName = $(this).attr('data-tab');
		$(this).closest(tabSection).find(tabBtn).removeClass('active-tab');
		$(this).addClass('active-tab');
		$(this).closest(tabSection).find(tabContent).hide();
		$("#"+selectTabName).show();
	});

	//Request For Quote Select Value
	//Payment Terms
	var paymentTermSelect = $('select.paymentterms'),
		paymentInput = $('input#paymentterms');
	paymentInput.val(paymentTermSelect.val());
	paymentTermSelect.change(function(){
		paymentInput.val(paymentTermSelect.val());
	});

	//Shipping Method
	var shippingMethodSelect = $('select.shippingmethod'),
		shippingInput = $('input#shippingmethod');
	shippingInput.val(shippingMethodSelect.val());
	shippingMethodSelect.change(function(){
		shippingInput.val(shippingMethodSelect.val());
	});


	//Default Accordion
	var accordContainer = $('.accord-sec'),
		accordTab = accordContainer.find('.accord-tab')
		accordContent = accordContainer.find('.accord-content');

	accordTab.click(function(){
		var selfClick = $(this);
		selfClick.toggleClass('accord-active');
		selfClick.parent(accordContainer).find(accordContent).slideToggle();
	});

	$('.notlogin.cms-index-index div.login-myaccount ul.inner li').removeClass('active-tab');

});

jQuery(window).load(function($){
	//Animate page on click of My account ribbin
	jQuery('body.userlogin div.policy').click(function(){
		var newarrivalPosition = jQuery('body.userlogin .login-myaccount').offset().top;
		jQuery('body,html').animate({scrollTop: newarrivalPosition}, 1000);
	});

	jQuery('.zopim:first').click(function(){
		
		if(!jQuery('.zopim').is(':visible')){
			jQuery('.hide-txt-patch').hide();
		}else{
			jQuery('.hide-txt-patch').show();
		};
	});

	jQuery('.yt-tab-listing').each(function(){
		var thisSec = jQuery(this);
		var divs = thisSec.find(".sub-item");
		for(var i = 0; i < divs.length; i+=4) {
			divs.slice(i, i+4).wrapAll("<div class='item item-tab-listing'><div class='row'></div></div>");
		}
		thisSec.find('.item-tab-listing:first').addClass('active');

		// Hide carosule arrows if less than 4 or equal itmes present
		if(divs.length <= 4){
			jQuery(this).find('.carousel-control-wrap').hide();
		}
	});
});
  