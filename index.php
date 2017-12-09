<?php
  session_start(); //Starts session.
  include_once "Resources/Components/InitSession.php";
  include_once "Resources/Components/Settings.php";

  if (isset($_GET['page'])){
    $page = $_GET['page'];
  } else {
      $page = 'CobblerApr';
  }

  if(isset($_GET['title'])){
    $title = $_GET['title'];
  } else {
    $title = $page;
  }

  // Clear cart.
  if($page == "ThanksChkout"){
     unset($_SESSION['cart']);
     unset($_SESSION['num_items']);
     unset($_SESSION['total']);
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link type="text/css" href="Resources/Css/reset.css" rel="stylesheet" />
    <link type="text/css" href="Resources/Css/tlaulli.css" rel="stylesheet" />
    <link type="text/css" href="Resources/Css/jquery-ui-1.9.0.custom.css" rel="stylesheet" />
    <link rel="stylesheet" href="Resources/Css/jquery.jqzoom.css" type="text/css">
    <script type="text/javascript" src="Resources/JavaScript/jquery-1.6.2.min.js"></script>
    <script type="text/javascript" src="Resources/JavaScript/jquery-ui-1.9.0.custom.min.js"></script>
    <script type="text/javascript" src="Resources/JavaScript/jquery.cluetip.min.js"></script>
    <script type="text/javascript" src="Resources/JavaScript/MathContext.js"></script>
    <script type="text/javascript" src="Resources/JavaScript/BigDecimal.js"></script>
    <script type="text/javascript" src="Resources/JavaScript/Tlaulli.js"></script>
    <script type="text/javascript" src="Resources/JavaScript/jquery.jqzoom-core.js" ></script>
    <title>Tlaulli - <?php echo $title; ?></title>
  </head>
  <body>
    <div id="main">
      <div id="header">
        <img src="Resources/Imgs/KernelsLogo.png" alt="Logo" style="position: relative; left: 30px; top: 18px"/>
        <img src="Resources/Imgs/Logo.JPG" alt="Tlaulli"/>
        <div class="main_financing">
          <script type="text/javascript" data-pp-pubid="b739a6657e" data-pp-placementtype="468x60"> 
            (function (d, t) {
              "use strict";
              var s = d.getElementsByTagName(t)[0], n = d.createElement(t);
              n.src = "//paypal.adtag.where.com/merchant.js";
              s.parentNode.insertBefore(n, s);
              }(document, "script"));
          </script>
        </div>

        <!-- <img src="Resources/Imgs/SubTitle.png" alt="Industrial and Home Wear"/> -->
        <a class="styled_link" href="javascript:changePage('ContactUs', 'Contact Us');">
          Contact Us
        </a>
        <span>
          <img src="Resources/Imgs/ShoppingCart.jpg" alt="Shopping Cart" style="margin-right: -10px;"/>
          <img src="Resources/Imgs/MetallicDisp.png" alt="Status Background"/>
          <span>
            Items:
            <span id="num_items">
               <?php echo ($_SESSION['num_items'] ? $_SESSION['num_items'] : 0); ?>
            </span>
            &nbsp;Total:&nbsp;
            <span id="total">
                $ <?php echo number_format($_SESSION['total'], 2); ?>
            </span>
          </span>
          <div id="checkout_icon">
            <a href="javascript:changePage('Checkout', 'Order Confirmation');">
              <img src="Resources/Imgs/Checkout.png" alt="Checkout"/>
            </a>
          </div>
        </span>
      </div>

      <div id="body">
        <ul id="nav">
          <!-- <li><a class="home" href="javascript:changePage('Home', 'Home');">Home</a></li> -->
          <li>
            <div>
              <span>
                <a class="aprons" href="javascript:changePage('CobblerApr', 'Cobbler Aprons');">Aprons</a>
              </span>
            </div>
            <ul>
              <li><a class="aprons_cobbler" href="javascript:changePage('CobblerApr', 'Cobbler Aprons');">Cobbler</a></li>
              <li><a class="aprons_bib" href="javascript:changePage('BibApr', 'Bib Aprons');">Bib</a></li>
              <!-- <li><a class="aprons_waist_down" href="javascript:changePage('WaistDownApr', 'Waist Down Aprons');">Waist Down</a></li> -->
              <li><a class="aprons_rounded_waist" href="javascript:changePage('RoundedWaistApr', 'Rounded Waist Aprons');">Rounded Waist</a></li>
              <!-- <li><a class="aprons_squared_waist" href="javascript:changePage('SquaredWaistApr', 'Squared Waist Aprons');">Squared Waist</a></li> -->
              <!-- <li><a class="aprons_butcher" href="javascript:changePage('ButcherApr', 'Butcher Aprons');">Butcher</a></li> -->
            </ul>
          </li>
          <li>
            <div>
              <span>
                <a class="uniforms" href="javascript:changePage('LaundryBags', 'Laundry Bags');">Laundry</a>
              </span>
            </div>
            <ul>
              <li><a class="uniforms_nurse" href="javascript:changePage('LaundryBags', 'Laundry Bags');">Denim Bag</a></li>
              <!-- <li><a class="uniforms_security" href="javascript:changePage('SecurityUni', 'Security Uniforms');">Security</a></li> -->
            </ul>
          </li>
          <!-- 
          <li>
            <div>
              <span>
                <a class="uniforms" href="javascript:changePage('SalonSal', 'All Salon Items');">Salon</a>
              </span>
            </div>
            <ul>
              <li><a class="salon_capes" href="javascript:changePage('CapesSal', 'Salon Capes');">Capes</a></li>
            </ul>
          </li>
          <li>
            <div>
              <span>
                <a class="accessories" href="javascript:changePage('AccessoriesAcc', 'Accessories');">Accessories</a>
              </span>
            </div>
            <ul>
              <li><a class="accessories_ties" href="javascript:changePage('TiesAcc', 'Ties');">Ties</a></li>
              <li><a class="accessories_earrings" href="javascript:changePage('EarringsAcc', 'Earrings');">Earrings</a></li>
            </ul>
          </li>
          <li>
            <div>
              <span>
                <a class="uniforms" href="javascript:changePage('RestaurantRest', 'Restaurant Supplies');">Restaurants</a>
              </span>
            </div>
            <ul>
              <li><a class="table_cloths" href="javascript:changePage('ClothsRest', 'Cloths');">Table Cloths</a></li>
              <li><a class="table_napkins" href="javascript:changePage('NapkinsRest', 'Napkins');">Table Napkins</a></li>
              <li><a class="bags_laundry_bags" href="javascript:changePage('LaundryBagsRest', 'Laundry Bags');">Laundry Bags</a></li>
            </ul>
          </li> -->
        </ul>

        <div id="content">
          <?php
          /*Pages */
            if (file_exists("Views/Aprons/{$page}.php")){
              include "Views/Aprons/{$page}.php";
            } elseif (file_exists("Views/Uniforms/{$page}.php")){
              include "Views/Uniforms/{$page}.php";
            } elseif (file_exists("Views/Accessories/{$page}.php")){
              include "Views/Accessories/{$page}.php";
            } elseif (file_exists("Views/Restaurant/{$page}.php")){
              include "Views/Restaurant/{$page}.php";
            } elseif (file_exists("Views/Bags/{$page}.php")){
              include "Views/Bags/{$page}.php";
            } elseif (file_exists("Views/Salon/{$page}.php")){
              include "Views/Salon/{$page}.php";
            } elseif (file_exists("Views/Checkout/{$page}.php")){
              include "Views/Checkout/{$page}.php";
            } elseif (file_exists("Views/System/{$page}.php")){
              include "Views/System/{$page}.php";
            } else {
              echo "The desired page was not found";
            }
          ?>
        </div>
        <br style="clear: both;"/>
      </div>

      <div id="footer">
          <a href="javascript:changePage('ReturnsExchanges', 'Returns & Exchanges');">Returns & Exchanges</a>
      </div>
    </div>
  </body>
</html>
