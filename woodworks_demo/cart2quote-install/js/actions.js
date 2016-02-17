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

var minimalTime = 2000;

$(function () {

    run()
});

function run() {
    var id = findNextAction();

    if (id != undefined) {
        executeAction(id);
    }
}

function findNextAction() {
    return $(".row[state='unchecked']").first().attr('id');
}

function executeAction(id) {
    var start = new Date().getTime();

    $.post('../actions.php', {action: id, url: window.location.pathname}).done(function (status) {
        status = status == 1 ? true : false;
        var time = determineWaitTime(determineElapsedTime(start));
        if (time > 0) {
            setTimeout(function () {
                setStatus(id, status);
            }, time);
        } else {
            setStatus(id, status);
        }
    });
}

function determineWaitTime(elapsedTime) {
    var waitTime = 0

    if (elapsedTime < minimalTime) {
        waitTime = minimalTime - elapsedTime;
    }
    return waitTime;
}

function determineElapsedTime(start) {
    var elapsed = new Date().getTime() - start;
    return elapsed;
}

function setStatus(id, status) {
    setStatusImage(id, status);
    setState(id, status);
    run();

}

function setStatusImage(id, status) {
    if (status) {
        $('#' + id).find("> img").attr('src', "../images/C2Q-pre-install-check_07.png");
    } else {
        $('#' + id).find("> img").attr('src', "../images/C2Q-pre-install-check_16.png");
    }
}

function setState(id, status) {
    $('#' + id).attr('state', status);
}

function openWindow(window_src) {
    window.open(window_src, 'newwindow', config = 'height=600,width=800, toolbar = no, menubar = no, scrollbars = no, resizable = no, location = no, directories = no, status = no');
}
