<?php
include_once "DbLib.php";

class Util {
    /*
     * Calculates and returns the optimum shipping cost.
     * 
     * Inputs: $count -- number of items to ship.
     * Returns: The cost to ship the items.
     */
    public static function getShippingCost($count){
        $cost_eighteens = 15.99;
        $cost_threes = 5.99;
        $cost_one = 2.99;
        $shipping_cost = 0;
        
        while ($count > 0) {
            if ($count <= 2) {
                $shipping_cost += ($cost_one * $count);
                $count = 0;
            } else if ($count >= 3 && $count <= 7) {
                $num_threes = floor($count / 3);
                $count -= ($num_threes * 3);
                $shipping_cost += $num_threes * $cost_threes;
            } else if ($count >= 8 && $count <= 17){
                $shipping_cost += $cost_eighteens;
                $count = 0;
            } else {
                $num_eighteens = floor($count / 18);
                $count -= ($num_eighteens * 18);
                $shipping_cost += $num_eighteens * $cost_eighteens;
            }
        }
        
        return $shipping_cost;
    }
    
    public static function getDiscountedCost($items, $total_items){
        $db = new DbLib();
        $special_discount = $db->getSpecialDiscount('buy_any_six_aprons_get_one_rw_free');
        $free_items = floor($total_items / ($special_discount->getQty() + 1));
        $entitled_free_items = floor($total_items / $special_discount->getQty());
        
        if($special_discount->isActive() && $free_items >= 1 &&
           Util::hasNumItems("apron", "rounded_waist", $free_items, $items)){
            $reduced_items = Util::removeNumItems("apron", "rounded_waist", $free_items, $items);
            return array("discounted_cost" => Util::applyWholeSaleDiscount($reduced_items),
                         "free_items" => $free_items,
                         "entitled_free_items" => $entitled_free_items);
        }
        
        return array("discounted_cost" => Util::applyWholeSaleDiscount($items),
                     "free_items" => 0,
                     "entitled_free_items" => $entitled_free_items);
    }
    
    // Returns a string with every word separated by an underscore in $str capitalized.
    public static function ucWord($str){
      $str_arr = explode("_", $str);
      $ret = "";
      
      foreach($str_arr as $idx => $val){
        $ret .= ucfirst($val) . " ";
      }
      return trim($ret);
    }
    
    public static function getPageInfo($type, $style){
        if($type == 'apron'){
            if($style == 'cobbler') {
                return array('CobblerApr', 'Cobbler Aprons', 'Cobbler');
            } else if($style == 'bib') {
                return array('BibApr', 'Bib Aprons', 'Bib');
            } else {
                return null;
            }
        }
    }
    
    // Returns the wholesale price for all the items in $items by item type and style.
    public static function applyWholeSaleDiscount($items){
      // Consolidate the counts for similarly-priced items.
        $item_count = array();
        $db = new DbLib();
        
        foreach($items as $id => $qty){
            $item = $db->getItem($id, false, false, false);
            
            if($item["item_type"] == "apron" &&
               ($item["style"] == "cobbler" || $item["style"] == "bib" ||
                $item["style"] == "rounded_waist") &&
               ($item["size_letter"] == "SP" || $item["size_letter"] == "S" ||
               $item["size_letter"] == "M" || $item["size_letter"] == "L" ||
               $item["size_letter"] == "XL")){
                // Currently, both double-sided and pechera have the same price scale.
                if(!isset($item_count[$item["style"]])){
                    $item_count[$item["style"]] = array($id, $qty);
                } else {
                    $item_count[$item["style"]][1] += $qty;
                }
            } else {
                return -1;
            }
        }
        
        // Determine the total discounted cost.
        $discounted_cost = 0;
        foreach($item_count as $style => $item_qty){
            $discounted_price = $db->getItemWsPrice($item_qty[0], $item_qty[1]);
            $discounted_cost += ($discounted_price * $item_qty[1]);
        }
        
        return $discounted_cost;
    }
    
    // Returns true if an item with $type and $style appears in $items $count number of times.
    public static function hasNumItems($type, $style, $count, $items){
      $num = 0;
      $db = new DbLib();
      foreach($items as $id => $qty){
        $item = $db->getItem($id, false, false, false);
            
        if($item["item_type"] == $type &&
           $item["style"] == $style){
          $num += $qty;
        }
      }
      
      if($num >= $count){
        return true;
      }
      
      return false;
    }
    
    public static function getSizeWord($size){
      switch($size){
        case "SP":
          return "X-Small";
        case "S":
          return "Small";
        case "M":
          return "Medium";
        case "L":
          return "Large";
        case "XL":
          return "X-Large";
        default:
          return null;
      }
    }
    
    /* Removes $count number of items with $type and $style from $items. */
    public static function removeNumItems($type, $style, $count, $items) {
      $db = new DbLib();
      foreach($items as $id => $qty){
        $item = $db->getItem($id, false, false, false);
            
        if($item["item_type"] == $type &&
           $item["style"] == $style){
          if($count >= $qty){
            $count -= $qty;
            unset($items[$id]); //All quantity of item used. No need to be in items.
          } else {
            $items[$id] -= $count;
            $count = 0;
          }
          
          if($count == 0){
            return $items;
          }
        }
      }
      
      return null; //Items doesn't have the desired count.
    }
}
?>
