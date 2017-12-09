<?php
include_once "DbBase.php";
include_once "SpecialDiscount.php";

class DbLib extends DbBase {
    public function getItem($id, $with_imgs = false, $with_ws_pricing = false, $with_sizes = false) {
      $sql = "SELECT pi.item_id, pi.prim_color, pi.sec_color, pi.item_type, pi.style, pi.pattern, pi.kwds, pi.des, 
                pi.unit_cost, pi.size_letter, pi.size_w, pi.size_h, pi.size_units,
                ci.prim_color AS trim_prim_color, ci.sec_color AS trim_sec_color, ci.pattern AS trim_pattern,
                ci.style As trim_style
              FROM item pi, item_item_aff iia, item ci WHERE pi.item_id=iia.parent_id AND 
                iia.child_id = ci.item_id AND iia.relation='bias' AND pi.item_id =" . $this->quote($id);
      $result = $this->query($sql);
      
      if($result->num_rows == 0){
         return null;
      }
      
      $item = $result->fetch_assoc();
      
      if($with_sizes){
        $sql = "SELECT pi.item_id, pi.size_letter, pi.size_w, pi.size_h, pi.size_units
              FROM item pi, item_item_aff iia, item ci WHERE pi.item_id=iia.parent_id AND 
                iia.child_id = ci.item_id AND iia.relation='bias'AND 
                pi.prim_color = " . $this->quote($item['prim_color']) . " AND pi.sec_color " .
               ($item['sec_color'] ? " = " : "") . $this->quote($item['sec_color']) .
               " AND pi.item_type = " . $this->quote($item['item_type']) . " AND pi.style = " . 
               $this->quote($item['style']) . " AND pi.pattern = " . $this->quote($item['pattern']) .
               " AND ci.prim_color = " . $this->quote($item['trim_prim_color']) . " AND ci.sec_color " .
               ($item['trim_sec_color'] ? " = " : "") . $this->quote($item['trim_sec_color']) .
               " AND ci.style = " . $this->quote($item['trim_style']) . " AND ci.pattern = " .
               $this->quote($item['trim_pattern']);
        $result = $this->query($sql);
        while($row = $result->fetch_assoc()){
         $item["sizes"][] = $row;
        }
      }
      
      if($with_imgs){
        $sql = "SELECT g.img_id, g.width, g.height, g.loc, g.des, g.perspective, g.img_type 
          FROM item i, item_img_aff iia, img g WHERE i.item_id = iia.item_id AND 
          iia.img_id = g.img_id AND i.item_id = " . $this->quote($id);
        $result = $this->query($sql);
        while($row = $result->fetch_assoc()){
         $item['imgs'][] = $row;
        }
      }
      
      if($with_ws_pricing){
        $item['ws_pricing'] = $this->getItemWsPricingScale($id);
      }
      
      return $item;
    }
    
    // Retrieves the wholesale pricing for an item.
    public function getItemWsPricingScale($id){
      $sql = "SELECT p.price_id, p.l_qty, p.h_qty, p.price FROM item i, item_sug_ws_price_aff ipa, sug_ws_price p
        WHERE i.item_id=ipa.item_id AND ipa.price_id=p.price_id AND i.item_id = " . $this->quote($id);
      $result = $this->query($sql);
      while($row = $result->fetch_assoc()){
         $item_pricing[] = $row;
      }
      return $item_pricing;
    }
    
    // Retrieves all item ids for groups where the item has a quantity greater than 0 or is always available.
    public function getItemGrpIds($item_type, $item_style){
      $sql = "SELECT i1.item_id FROM item i1, item_item_aff ii, item i2 WHERE i1.item_id = ii.parent_id AND 
         ii.child_id = i2.item_id AND ii.relation = 'bias' AND i1.item_type = " . $this->quote($item_type) .
         " AND i1.style=" . $this->quote($item_style) . " AND 
            (i1.cur_qty > 0 OR EXISTS (SELECT * FROM always_avail WHERE item_id = i1.item_id LIMIT 1))
         GROUP BY i1.prim_color, i1.sec_color, i1.item_type, i1.style, i1.pattern,
                   i2.prim_color, i2.sec_color, i2.item_type, i2.style, i2.pattern";
      
      $result = $this->query($sql);
      $ret = array();
      while($row = $result->fetch_row()){
         $ret[] = $row[0];
      }
      
      return $ret;
    }
    
    public function getItemWsPrice($id, $qty){
      $sql = "SELECT p.price FROM item i, item_sug_ws_price_aff ipa, sug_ws_price p
        WHERE i.item_id=ipa.item_id AND ipa.price_id=p.price_id AND i.item_id = " . $this->quote($id) . 
        " AND ((" . $this->quote($qty) . " BETWEEN p.l_qty AND p.h_qty) OR (" .
                 $this->quote($qty) . " > p.l_qty AND p.h_qty IS NULL))";
      $item_pricing = $this->query($sql)->fetch_row();
      return $item_pricing[0];
    }
    
    public function getThumbNail($id){
      $sql = "SELECT g.img_id, g.width, g.height, g.loc, g.des, g.perspective, g.img_type 
          FROM item_img_aff iia, img g WHERE  
          iia.img_id = g.img_id AND g.img_type = 'thumbnail' AND iia.item_id = " . $this->quote($id);
      $result = $this->query($sql)->fetch_assoc();
      return $result;
    }
    
    // Decrements the cur_qty of the item with $id by $qty. Returns TRUE if successful, FALSE if not.
    public function decrementItemQty($id, $qty = 1){
       if($this->alwaysAvail($id)){
          $sql = "UPDATE item SET cur_qty = cur_qty - {$qty} WHERE item_id = " . $this->quote($id);
          return $this->query($sql);
       }
       
       // Ensure that you don't sell what you don't have.
       if(!$this->itemExists($id, $qty)){
          return false;
       }

       $sql = "UPDATE item SET cur_qty = cur_qty - {$qty} WHERE item_id = " . $this->quote($id);
       return $this->query($sql);
    }
    
    // Returns TRUE if the item with $id and $qty exists; FALSE otherwise.
    public function itemExists($id, $qty = null){
      $sql = "SELECT * FROM item WHERE item_id = " . $this->quote($id) . 
              ($qty != null ? " AND cur_qty >= {$qty}" : "") . " LIMIT 1";
      return $this->query($sql)->num_rows;
    }
    
    // Returns TRUE if the item is one that we always have on hand; FALSE otherwise.
    public function alwaysAvail($id){
      $sql = "SELECT * FROM always_avail WHERE item_id = " . $this->quote($id) . " LIMIT 1";
      return $this->query($sql)->num_rows;
    }
    
    // Returns the items lowest price on the whole sale scale.
    public function getItemBestPrice($id){
        $sql = "SELECT p.price FROM item i, item_sug_ws_price_aff ipa, sug_ws_price p
            WHERE i.item_id=ipa.item_id AND ipa.price_id=p.price_id AND i.item_id = " . $this->quote($id) . 
            " AND p.h_qty IS NULL";
        $item_pricing = $this->query($sql)->fetch_row();
        return $item_pricing[0];
    }
    
    // Gets the price for one item.
    public function getItemRetailPrice($id){
        $sql = "SELECT p.price FROM item i, item_sug_ws_price_aff ipa, sug_ws_price p
            WHERE i.item_id=ipa.item_id AND ipa.price_id=p.price_id AND i.item_id = " . $this->quote($id) . 
            " AND p.l_qty = 1";
        $item_pricing = $this->query($sql)->fetch_row();
        return $item_pricing[0];
    }
    
    public function getSpecialDiscount($name){
        $sql = "SELECT * FROM special_discounts WHERE name = " . $this->quote($name) . " LIMIT 1";
        $result = $this->query($sql)->fetch_assoc();
        
        return new SpecialDiscount($result);
    }
}
?>
