<?php
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
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="../js/tooltip.js"></script>
    <script src="../js/actions.js"></script>

    <title>Check</title>
</head>
<body>
<div class="container">
    <div id="header">
        <img src="../images/C2Q-pre-install-check_03.png" id="logo"/>
    </div>
    <div id="content">
        <div class="row" id="disable_compiler" state="unchecked">
            <img src="../images/C2Q-pre-install-check_21.gif" class="state"/>
            <span>Disabled compiler mode</span>
        </div>
        <div class="row" id="disable_cache" state="unchecked">
            <img src="../images/C2Q-pre-install-check_21.gif" class="state"/>
            <span>Disabled cache</span>
        </div>
        <div class="row" id="clean_cache" state="unchecked">
            <img src="../images/C2Q-pre-install-check_21.gif" class="state"/>
            <span>Cleaned cache</span>
        </div>
        <div class="row" id="check_ioncube">
            <img src="../images/C2Q-pre-install-check_10.png" class="follow"/>
                <span>
                    <a href="#" onclick="openWindow('../ioncube/loader-wizard.php');">Check for IonCube loader</a>
                    <img src="../images/C2Q-pre-install-check_12.png" class="icon"
                         title="This link will check your current IonCube loader installation. You will be notified if your installation is correct or any errors that have occurred and how to fix these."
                         rel="tooltip"/>
                </span>
        </div>
    </div>
</div>
</body>
</html>
