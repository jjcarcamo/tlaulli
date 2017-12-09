<?php
  include_once 'DbLib.php';
  include_once 'Util.php';
  
  $db_lib = new DbLib();
  if(isset($_GET['getItem'])){
    $result = $db_lib->getItem($_GET['getItem']['id'], ($_GET['getItem']['with_imgs'] ? true : false),
      ($_GET['getItem']['with_ws_pricing'] ? true : false), ($_GET['getItem']['with_sizes'] ? true : false));
    echo json_encode($result);
  } else if(isset($_GET['getShippingCost'])){
    $result = Util::getShippingCost($_GET['getShippingCost']['count']);
    echo json_encode(array('shipping_cost' => $result));
  } else if(isset($_GET['getItemWsPricing'])){
    $result = $db_lib->getItemWsPricing($_GET['getItemWsPricing']['id']);
    echo json_encode($result);
  } else if(isset($_GET['getDiscountedCost'])) {
    $result = Util::getDiscountedCost($_GET['getDiscountedCost']['items'], $_GET['getDiscountedCost']['total_items']);
    echo json_encode($result);
  } else {
    echo json_encode(array("error" => "Invalid operation"));
  }
?>
