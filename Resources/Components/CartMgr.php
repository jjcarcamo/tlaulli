<?php
  session_start();
  include_once '../Libs/DbLib.php';
  include_once '../Libs/Util.php';
  header('Cache-Control: no-cache, must-revalidate');
  header('Content-type: application/json');
  
  if(isset($_GET['add']) && isset($_GET['id']) && isset($_GET['qty'])){
    $db = new DbLib();
    
    if(isset($_SESSION['cart'][$_GET['id']])){
      $_SESSION['cart'][$_GET['id']] += $_GET['qty'];
    } else {
      $_SESSION['cart'][$_GET['id']] = $_GET['qty'];
    }
    
    $_SESSION['num_items'] += $_GET['qty'];
    $result = Util::getDiscountedCost($_SESSION["cart"], $_SESSION['num_items']);
          
    if($result["discounted_cost"] <= 0){
        echo "<script>changePage('Error', 'Error');</script>";
        return;
    }
    
    $_SESSION['total'] = $result["discounted_cost"];
    echo json_encode(array('num_items' => $_SESSION['num_items'], 'total' => $_SESSION['total']));
  } else if(isset($_GET['update']) && isset($_GET['id']) && isset($_GET['qty'])){
    $db = new DbLib();
    $_SESSION['num_items'] -= $_SESSION['cart'][$_GET['id']];
    $_SESSION['num_items'] += $_GET['qty'];
    $_SESSION['cart'][$_GET['id']] = $_GET['qty'];
    
    if($_SESSION['num_items'] == 0){
        $total = 0;
    } else {
        $result = Util::getDiscountedCost($_SESSION["cart"], $_SESSION['num_items']);
        $total = $result["discounted_cost"];
        
        if($total <= 0){
            echo "<script>changePage('Error', 'Error');</script>";
            return;
        }
    }
    
    $_SESSION['total'] = $total;
    echo json_encode(array('num_items' => $_SESSION['num_items'], 'total' => $_SESSION['total']));
  } else if(isset($_GET['delete']) && isset($_GET['id'])){
    $db = new DbLib();
    $_SESSION['num_items'] -= $_SESSION['cart'][$_GET['id']];
    unset($_SESSION['cart'][$_GET['id']]);
    
    if($_SESSION['num_items'] == 0){
        $total = 0;
    } else {
        $result = Util::getDiscountedCost($_SESSION["cart"], $_SESSION['num_items']);
        $total = $result["discounted_cost"];
        
        if($total <= 0){
            echo "<script>changePage('Error', 'Error');</script>";
            return;
        }
    }
          
    $_SESSION['total'] = $total;
    echo json_encode(array('num_items' => $_SESSION['num_items'], 'total' => $_SESSION['total']));
  }
  else {
    echo json_encode(array('error' => 'Invalid URL parameters: ' + print_r($_GET, true)));
  }
?>
