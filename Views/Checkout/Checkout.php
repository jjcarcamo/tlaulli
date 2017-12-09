<?php
  include_once 'Resources/Libs/DbLib.php';
  include_once 'Resources/Libs/Util.php';
  include_once 'Resources/Components/Settings.php';
?>
<div id="checkout">
  <div class="page_title">
    <img src="Resources/Imgs/TitleBarCheckout.JPG" alt="Checkout" />
    <br style="clear: both;"/>
  </div>
  <div class="page_body">
    <?php 
      if(!isset($_SESSION["cart"]) || count($_SESSION["cart"]) == 0){
            echo "<tr><td colspan='5'>There are no items in your cart. Please shop for items.</td></tr>";
    } else {
    ?>
    <form action="<?php echo $paypal_server; ?>" method="post">  
      <table id="summary">
        <thead>
          <tr>
            <th>
              <!-- Apron image placeholder -->
            </th>
            <th>
              Item
            </th>
            <th>
              Quantity
            </th>
            <th nowrap>
              Retail Price
            </th>
            <th>
              Amount
            </th>
            <th>
              <!-- Delete image placeholder -->
            </th>
          </tr>
        </thead>
        <tbody>
        <?php
          $db = new DbLib();
          $count = 1;
          $total_price = 0;
          foreach($_SESSION["cart"] as $id => $qty){
            $item = $db->getItem($id, true, false, false);
            $retail_price = $db->getItemRetailPrice($id);
            $item_name = Util::ucWord($item['style']) . " " . Util::ucWord($item['item_type']) . " - " .
              ($item['style'] != 'rounded_waist' ? Util::getSizeWord($item['size_letter']) : "One Size Fits All") .
              " " . Util::ucWord($item['pattern']) . " " . Util::ucWord($item['prim_color']) .
              ($item['sec_color'] ? " with " . Util::ucWord($item['sec_color']) : "") . " w/ " .
               Util::ucWord($item['trim_style']) . " " .
               Util::ucWord($item['trim_prim_color']) . ($item['trim_sec_color'] != null ? " and " . 
               Util::ucWord($item['trim_sec_color']) : "") . " " . Util::ucWord($item['trim_pattern']) .
                  " Trim (ID: {$id})";
            $item_price = $qty * $retail_price;
            $total_price += $item_price;
            echo "<tr id='{$id}'>";
            echo "<td><a class='sticky' rel='{$item['imgs'][0]['loc']}'>
                        <img src='" . str_replace("Nm.JPG", "Tn.JPG", $item['imgs'][0]['loc']) . "' alt='Thumbnail'/>
                      </a>
                  </td>
              <td class='data'>{$item_name}</td>
              <td class='data'><input type='text' size='3' value='{$qty}' class='chko_qty'/></td>
              <td class='data'>{$retail_price}</td>
              <td class='data'><span class='summary_amount'>" . number_format($item_price, 2) . "</span></td>
              <td class='data'><a href='#' class='chko_delete'><img src='Resources/Imgs/XMark.jpg' alt='Delete'/></a></td>
              <input type='hidden' name='item_name_{$count}' value='{$item_name}'> 
                <input type='hidden' name='amount_{$count}' value='{$retail_price}'>
                <input type='hidden' name='quantity_{$count}' value='{$qty}'>
                <input type='hidden' name='item_number_{$count}' value='{$id}'>
              </tr>";
            ++$count;
          }

          // Determine shipping cost.
          $shipping_cost = Util::getShippingCost($_SESSION['num_items']);

          // Determine discounted cost.
          $result = Util::getDiscountedCost($_SESSION["cart"], $_SESSION['num_items']);
          
          if($result["discounted_cost"] <= 0){
              echo "<script>changePage('Error', 'Error');</script>";
              return;
          }
          
          $discount = $total_price - $result["discounted_cost"];
          $tax = number_format($result["discounted_cost"] * $tax_rate_cfg, 2);
        ?>
        </tbody>
      </table>
<!--      <table id="message">
        <tr>
          <td id="congrats">
            <?php if($result["free_items"] > 0): ?>
                Congratulations. You are getting <?php echo $result["free_items"]; ?> 
                  free apron<?php echo ($result["free_items"] > 1 ? "s!" : "!") ?>
           <?php endif; ?>
          </td>
        </tr>
        <tr>
          <td id="upsell">
            <?php if($result["entitled_free_items"] > $result["free_items"]): ?>
                <?php $additional = $result["entitled_free_items"] - $result["free_items"]; ?>
                <br/>Congratulations. You qualify for 
                  <?php 
                    if($result["free_items"] > 0) {
                      echo "an additional ";
                    }
                 ?>
                  <?php echo $additional; ?> 
                  free apron<?php echo ($additional > 1 ? "s" : "") ?>. 
                  Would you like to get <?php echo ($additional > 1 ? "them" : "it") ?> 
                  now? <input type="button" value="Yes" id="yes_free_apron" class="button"/>
           <?php endif; ?>
          </td>
        </tr>
      </table>-->
      <table id="summary_calc">
        <tr>
          <td class="calc_heading">Total Amount: </td>
          <td class="calc_data"><span id="summary_total_amt"><?php echo number_format($total_price, 2); ?></span></td></tr>
        <tr>
        <tr>
          <td class="calc_heading savings">Discount: </td>
          <td class="calc_data">(<span id="summary_discount"><?php echo number_format($discount, 2); ?></span>)</td>
        </tr>
        <tr>
          <td class="calc_heading">Subtotal: </td>
          <td class="calc_data">
              <span id="summary_subtotal">
                  <?php echo number_format($result["discounted_cost"], 2); ?>
              </span>
          </td>
        </tr>
        <tr>
          <td class="calc_heading">Tax: </td>
          <td class="calc_data">
              <span id="summary_tax">
                  <?php echo number_format($tax, 2); ?>
              </span>
          </td>
        </tr>
        <tr>
          <td class="calc_heading">Shipping and Handling: </td>
          <td class="calc_data"><span id="summary_sh"><?php echo number_format($shipping_cost, 2); ?></span></td>
        </tr>
        <tr>
          <td class="calc_heading">Total: </td>
          <td class="calc_data">
              <span id="summary_total">
                  <?php echo "$ " . number_format($result["discounted_cost"] + $shipping_cost + $tax, 2); ?>
              </span>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <input type="button" value="Continue Shopping" id="cont_shop" class="button"/>&nbsp;
            <input type="submit" value="Payment" class="button"/><br/>
            <div class="checkout_financing">
              <script type="text/javascript" data-pp-pubid="b739a6657e" data-pp-placementtype="170x100">
                (function (d, t) {
                  "use strict";
                  var s = d.getElementsByTagName(t)[0], n = d.createElement(t);
                  n.src = "//paypal.adtag.where.com/merchant.js";
                  s.parentNode.insertBefore(n, s);
                  }(document, "script"));
              </script>
            </div>
          </td>
        </tr>
          <?php
              echo "<input type='hidden' name='shipping_1' value='{$shipping_cost}'>";
              echo "<input type='hidden' name='discount_amount_cart' value='{$discount}'>";
              echo "<input type='hidden' name='tax_cart' value='{$tax}'>";
              echo "<input type='hidden' name='tax_rate' value='{$tax_rate_cfg}'>";
          ?>
      </table>
      <input type="hidden" name="cmd" value="_cart">
      <input type="hidden" name="upload" value="1">
      <input type="hidden" name="business" value="<?php echo $paypal_email; ?>">
      <input type="hidden" name="return" value="http://www.tlaulli.com?page=ThanksChkout&title=Thank You">
      <input type="hidden" name="notify_url" value="http://www.tlaulli.com/Views/Checkout/NotifyChkout.php">
    </form>
      
    <?php
      }
    ?>
  </div>
</div>
