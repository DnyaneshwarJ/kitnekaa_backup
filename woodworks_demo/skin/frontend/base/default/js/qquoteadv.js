/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2015] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2015 Cart2Quote B.V. (http://www.cart2quote.com)
 * @license     http://www.cart2quote.com/ordering-licenses
 */

function prepareIE(height, overflow) {
    bod = document.getElementsByTagName('body')[0];
    bod.style.height = height;
    bod.style.overflow = overflow;

    htm = document.getElementsByTagName('html')[0];
    htm.style.height = height;
    htm.style.overflow = overflow;
}

function initMsg() {
    bod = document.getElementsByTagName('body')[0];
    overlay = document.createElement('div');
    overlay.id = 'overlay';

    bod.appendChild(overlay);
    $('overlay').style.display = 'block';
    try {
        $('lightbox1').style.display = 'block';
    } catch (e) {
    }
    prepareIE("auto", "auto");
}
function cancelMsg() {
    bod = document.getElementsByTagName('body')[0];
    olddiv = document.getElementById('overlay');
    if (overlay) {
        bod.removeChild(olddiv);
    }
}

function addQuote(url, ajax) {
    frmAdd2Cart = $('product_addtocart_form');
    if (frmAdd2Cart) {
        var validator = new Validation(frmAdd2Cart);
        if (validator)
            if (validator.validate()) {
                if (ajax == 1) {
                    initMsg()
                    lightbox2 = document.createElement('div');
                    lightbox2.id = "lightbox2";
                    overlay.appendChild(lightbox2);

                    lightboxload = document.createElement('div');
                    lightboxload.id = "lightboxload";
                    lightbox2.appendChild(lightboxload);
                    frmValues = frmAdd2Cart.serialize(true);
                    new Ajax.Request(url, {
                        parameters: frmValues,
                        method: 'post',
                        evalJSON: 'force',
                        onSuccess: function (transport) {
                            data = transport.responseJSON;
                            if (data['result'] == 1) {
                                $$('a.top-link-qquoteadv')[0].update(data['itemstext']);
                                document.getElementById('lightbox2').innerHTML = data['html'];
                            } else {
                                document.location.href = data['producturl']
                            }
                        }
                    });
                } else {
                    frmAdd2Cart.writeAttribute('action', url);
                    frmAdd2Cart.submit();
                }
            }
    }
}

function addQuoteList(url, ajax) {

    if (url.indexOf("c2qredirect") != -1) {
        document.location.href = url;
    } else {

        if (ajax == 1) {
            initMsg()
            lightbox2 = document.createElement('div');
            lightbox2.id = "lightbox2";
            overlay.appendChild(lightbox2);

            lightboxload = document.createElement('div');
            lightboxload.id = "lightboxload";
            lightbox2.appendChild(lightboxload);


            new Ajax.Request(url, {
                method: 'post',
                evalJSON: 'force',
                onSuccess: function (transport) {
                    data = transport.responseJSON;
                    if (data['result'] == 1) {
                        $$('a.top-link-qquoteadv')[0].update(data['itemstext']);
                        document.getElementById('lightbox2').innerHTML = data['html'];
                    } else {
                        document.location.href = data['producturl']
                    }
                }
            });
        } else {
            frmAdd2Cart.writeAttribute('action', url);
            frmAdd2Cart.submit();
        }
    }

}

function isExistUserEmail(event, url, errorMsg) {
    elmEmail = Event.element(event);
    elmEmailMsg = $('email_message');
    loaderEmailDiv = $("please-wait");
    btnSubm = $('submitOrder')

    if (btnSubm) {
        btnSubm.disabled = false;
    }

    val = $F(elmEmail);  //$F('customer:email');
    var pars = 'email=' + val;

    //loader
    loaderEmailDiv.show();

    new Ajax.Request(url, {
        method: 'post',
        parameters: pars,
        //onCreate: function() {  },
        onSuccess: function (transport) {
            var responseStr = transport.responseText;
            if (responseStr == 'exists') {
                elmEmailMsg.show();
                elmEmailMsg.innerHTML = errorMsg;
                elmEmailMsg.addClassName("validation-advice");

                if ($('advice-required-entry-customer:email')) $('advice-required-entry-customer:email').hide();
                if ($('advice-validate-email-customer:email')) $('advice-validate-email-customer:email').hide();

                elmEmail.addClassName('validation-failed');
                if (btnSubm) {
                    btnSubm.setStyle({background: '#dddddd'});
                    btnSubm.disabled = true;
                }

            } else {
                elmEmailMsg.hide();
                elmEmailMsg.removeClassName("validation-advice");
            }
            loaderEmailDiv.hide();
        },
        onFailure: function () {
            loaderEmailDiv.hide();
            alert('Connection Error. Try again later.');
        }
    });

    return(false);
}

function adminLogin(url) {
    var windowHandle = popupwindow(url, '_blank', 850, 600);

    var timer = setInterval(function () {
        if (!windowHandle || windowHandle.closed) {
            clearInterval(timer);
            return;
        }

        if (windowHandle.location.href.substr(0, 4) != 'http') {
            return;
        }

        if (windowHandle.location.href == url) {
            return;
        }

        windowHandle.close();
        window.location.reload();
        clearInterval(timer);
    }, 50);
}

function popupwindow(url, title, w, h) {
    var left = (screen.width / 2) - (w / 2);
    var top = (screen.height / 2) - (h / 2);
    return window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
}

/**
 * This function fixes an issue with Magento CE 1.9.1.0 (And probably Magento EE 1.14.1.0)
 * On configurable products the onclick on tje Add to Quote button got replaced by the
 * onclick from the Add to Cart button.
 */
function overWriteConfigurableSwatches(){
    // rewrite the setStockData method from /js/configurableswatches/swatches-product.js; it would also select the quote button
    if(typeof Product !== "undefined") {
        if(typeof Product.ConfigurableSwatches === "object") {
            if (typeof(Product.ConfigurableSwatches.prototype) === "object") {
                //only overwrite if it exists
                Product.ConfigurableSwatches.prototype.setStockData = function () {
                    //var cartBtn = $$('.add-to-cart button.button');
                    var cartBtn = $$('.add-to-cart button.button:not(.btn-quote)');
                    this._E.cartBtn = {
                        btn: cartBtn,
                        txt: cartBtn.invoke('readAttribute', 'title'),
                        onclick: cartBtn.length ? cartBtn[0].getAttribute('onclick') : ''
                    };
                    //add quote button
                    var qtBtn = $$('.add-to-cart button.button.btn-quote');
                    this._E.qtBtn = {
                        btn: qtBtn,
                        txt: qtBtn.invoke('readAttribute', 'title'),
                        onclick: qtBtn.length ? qtBtn[0].getAttribute('onclick') : ''
                    };
                    this._E.availability = $$('p.availability');
                    // Set cart button event
                    this._E.cartBtn.btn.invoke('up').invoke('observe', 'mouseenter', function () {
                        clearTimeout(this._N.resetTimeout);
                        this.resetAvailableOptions();
                    }.bind(this));
                    // Set Quote button event
                    this._E.qtBtn.btn.invoke('up').invoke('observe', 'mouseenter', function () {
                        clearTimeout(this._N.resetTimeout);
                        this.resetAvailableOptions();
                    }.bind(this));
                };

                Product.ConfigurableSwatches.prototype.setStockStatus = function (inStock) {
                    if (inStock) {
                        this._E.availability.each(function (el) {
                            var el = $(el);
                            el.addClassName('in-stock').removeClassName('out-of-stock');
                            el.select('span').invoke('update', Translator.translate('In Stock'));
                        });

                        this._E.cartBtn.btn.each(function (el, index) {
                            var el = $(el);
                            el.disabled = false;
                            el.removeClassName('out-of-stock');
                            el.writeAttribute('onclick', this._E.cartBtn.onclick);
                            el.title = '' + Translator.translate(this._E.cartBtn.txt[index]);
                            el.select('span span').invoke('update', Translator.translate(this._E.cartBtn.txt[index]));
                        }.bind(this));
                        this._E.qtBtn.btn.each(function (el, index) {
                            var el = $(el);
                            el.disabled = false;
                            el.removeClassName('out-of-stock');
                            el.writeAttribute('onclick', this._E.qtBtn.onclick);
                            el.title = '' + Translator.translate(this._E.qtBtn.txt[index]);
                            el.select('span span').invoke('update', Translator.translate(this._E.qtBtn.txt[index]));
                        }.bind(this));
                    } else {
                        this._E.availability.each(function (el) {
                            var el = $(el);
                            el.addClassName('out-of-stock').removeClassName('in-stock');
                            el.select('span').invoke('update', Translator.translate('Out of Stock'));
                        });
                        this._E.cartBtn.btn.each(function (el) {
                            var el = $(el);
                            el.addClassName('out-of-stock');
                            el.disabled = true;
                            el.removeAttribute('onclick');
                            el.observe('click', function (event) {
                                Event.stop(event);
                                return false;
                            });
                            el.writeAttribute('title', Translator.translate('Out of Stock'));
                            el.select('span span').invoke('update', Translator.translate('Out of Stock'));
                        });
                        this._E.qtBtn.btn.each(function (el) {
                            var el = $(el);
                            el.addClassName('out-of-stock');
                            el.disabled = true;
                            el.removeAttribute('onclick');
                            el.observe('click', function (event) {
                                Event.stop(event);
                                return false;
                            });
                            el.writeAttribute('title', Translator.translate('Out of Stock'));
                            el.select('span span').invoke('update', Translator.translate('Out of Stock'));
                        });
                    }
                }

                //execute setStockData again to reset the observers
                Product.ConfigurableSwatches.prototype.setStockData();
            }
        }
    }
}

document.observe("dom:loaded", function() {
    overWriteConfigurableSwatches();
});