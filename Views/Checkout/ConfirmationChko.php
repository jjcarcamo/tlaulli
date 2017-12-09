<?php
  include_once 'Resources/Libs/DbLib.php';
?>
<div id="confirmation">
  <?php 
    if(!isset($_SESSION["cart"]) || count($_SESSION["cart"]) == 0){
          echo "<tr><td colspan='5'>There are no items in your cart. Please shop for items.</td></tr>";
  } else {
  ?>
  <form action="https://www.paypal.com/cgi-bin/webscr" method="post">  
    <table id="summary">
      <thead>
        <tr>
          <th>
            Item
          </th>
          <th>
            Quantity
          </th>
          <th>
            Price
          </th>
          <th>
            Total
          </th>
          <th>
          </th>
        </tr>
      </thead>
      <tbody>
      <?php
        $db = new DbLib();
        $count = 1;
        foreach($_SESSION["cart"] as $id => $qty){
          $item = $db->getItem($id, true, false, true);
          $item_name = "{$item['style']} {$item['item_type']} - {$item['sizes'][0]['letter']} {$item['pattern']} 
            {$item['prim_color']}" . ($item['sec_color'] ? " with " . $item['sec_color'] : "");
          echo "<tr id='{$id}'>
            <td>{$item_name}</td>
            <td><input type='text' size='3' value='{$qty}' class='chko_qty'/></td>
            <td>{$item['sug_retail_price']}</td>
            <td><span class='chko_subtotal'>" . ($qty * $item['sug_retail_price']) . "</span></td>
            <td><a href='#' class='chko_delete'><img src='Resources/Imgs/XMark.jpg' alt='Delete'/></a></td>
            <input type='hidden' name='item_name_{$count}' value='{$item_name}'> 
              <input type='hidden' name='amount_{$count}' value='{$item['sug_retail_price']}'>
              <input type='hidden' name='quantity_{$count}' value='{$qty}'>
              <input type='hidden' name='tax_rate_{$count}' value='8.75'>
            </tr>";
          ++$count;
        }
      ?>
      <tr>
        <td colspan="3"></td>
        <td>Total: </td>
        <td><span class="chko_total"><?php echo $_SESSION['total'] ?></span></td></tr>
      <tr>
        <td></td>
        <td colspan="4">
          <input type="button" value="Continue Shopping" id="cont_shop"/>&nbsp;
          <input type="submit" value="Payment"/>
        </td>
      </tr>
      </tbody>
    </table>
    <input type="hidden" name="cmd" value="_cart"> 
    <input type="hidden" name="upload" value="1"> 
    <input type="hidden" name="business" value="joe.carcamo@gmail.com">
  </form>
  <?php
    }
  ?>
</div>
