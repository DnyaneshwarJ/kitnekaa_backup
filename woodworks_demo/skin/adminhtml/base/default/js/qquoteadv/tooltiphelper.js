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
 * @discription This is the AJAX function that displays the body of an article in a tooltip (the [?] icon) in the Quote settings of Cart2Quote.
 */

/**
 * On page load get the article list
 */
document.observe("dom:loaded", function () {
    getArticleList();
});

/**
 *
 * @param tooltipElement
 * @param articleId The article ID given in the HTML
 * @param articleList List of articles that is request by Zendesk.
 */

function getArticleContent(tooltipElement, articleId, articleList) {
    var body = "Not available at this moment"; // Error
    if(articleList !== undefined){
        for (var key in articleList) {
            if(articleList[key].id == articleId){
                body = articleList[key].body;
                break;
            }
        }
    }
    setArticleContent(tooltipElement, body);
}

/**
 * Request a article list from Zendesk.
 */
function getArticleList(){
    var baseURL = $('ttfu').readAttribute('href');

    new Ajax.Request(baseURL + "/qquoteadv/requestArticle", {
        method: 'post',
        asynchronous: true,
        loaderArea:false,
        onSuccess: function (response) {
            var articleList = response.responseText.evalJSON(true);
            $$(".field-tooltip").invoke('observe', 'mouseover', function (event) {
                var articleId = getArticleId(this);
                if (articleId !== undefined) {
                    getArticleContent(this, articleId, articleList);
                }
            });
        },
        onFailure: function(){
            console.log("tooltiphelper.js: getArticle Error");
        }
    });
}

/**
 * Sets the article content
 * @param tooltipElement
 * @param body
 */

function setArticleContent(tooltipElement, body) {
    tooltipElement.firstChild.innerHTML = body;
}
/**
 * Get the article ID from the tooptip element.
 * @param tooltipElement
 * @returns articleId
 */

function getArticleId(tooltipElement) {
    var articleId;
    var articleIdInputElement = tooltipElement.getElementsByClassName('article_id')[0];
    if (articleIdInputElement !== undefined) {
        articleId = articleIdInputElement.value;
    }
    return articleId;
}