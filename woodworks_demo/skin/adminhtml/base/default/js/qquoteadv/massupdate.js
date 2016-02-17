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

/**
 * Created by Bram van Dooren on 12-02-14.
 */

document.observe("dom:loaded", function () {

    $$("#mass_update_button").invoke('observe', 'click', function (event) {
        massUpdateAllowToQuote();
    });
});

function massUpdateAllowToQuote() {
    var url = $('mass_update_button').readAttribute('url');
    var quote_mode = $('qquoteadv_advanced_settings_mass_update_mass_update_cart2quote_attributes').getValue();
    var ranges = $('qquoteadv_advanced_settings_mass_update_mass_update_cart2quote_attribute_ranges').getValue();

    new Ajax.Request(url, {
        method: 'post',
        parameters: {quote_mode: quote_mode, ranges: ranges  },
        onSuccess: function (response) {
            //alert(response.responseText);
            location.reload();
        }
    });
}
