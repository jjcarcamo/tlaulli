<?php
include_once 'DbLib.php';
include_once 'Util.php';

class display{
  public static function displayApron($style){
      $db = new DbLib();
      $ids = $db->getItemGrpIds('Apron', $style);
      
      $count = 1;
      $output = "";
      foreach($ids as $key => $id){
        // Fetch item group.
        $item = $db->getItem($id, true, true, true);
        $output .= "<div class='single_item_container' id='{$id}'>
            <div class='item_price'><img src='Resources/Imgs/{$item['ws_pricing'][0]['price']}Dollars.JPG'/></div>
            <div class='show_details'>View Details</div>
          <img src='{$item['imgs'][0]['loc']}' class='has_details' alt='" . Util::ucWord(implode(' ', explode('_', $item['style']))) 
            . " " . Util::ucWord($item['prim_color']) . " " .  ($item['sec_color'] != null ? " and " . 
            Util::ucWord($item['sec_color']) : "") . " " . Util::ucWord($item['pattern']) .
            " w/ " . Util::ucWord($item['trim_style']) . " " .
            Util::ucWord($item['trim_prim_color']) . ($item['trim_sec_color'] != null ? " and " . 
            Util::ucWord($item['trim_sec_color']) : "") . " " . Util::ucWord($item['trim_pattern']) .
                  " Trim Apron" . "'/>" .
            
          "<div>";
          
        // Currently there is only a one-size-fits-all for rounded waist.
        $output .= "<label class='label'>Size:</label>";
        if($item['style'] != 'rounded_waist'){
          $output .= "<select class='size'>";
        
          foreach($item['sizes'] as $key => $size){
            if($size['size_letter'] != "SP"){
              $output .= "<option value='{$size['item_id']}'>" .
                Util::getSizeWord($size['size_letter']) . "</option>";
            }
          }
        } else {
          $output .= "<select class='size' disabled='disabled'>
                        <option value='{$id}'>
                          One Size Fits All
                        </option>";
        }
        $output .= "</select>
          </div>
          <div>
            <label class='label'>Quantity:</label>
            <input class='qty' type='text' name='qty'/>
          </div>
          <input class='add_to_cart button' type='button' value='Add to Cart'/>
        </div>";
                    
        if($count % 4 == 0){
          echo "<div>" . $output . "<br style='clear: both'/></div>";
          $output = "";
        }
        
        ++$count;
      }
      
      if($output != ""){
        echo "<div>" . $output . "<br style='clear: both'/></div>";
      }
  }
  
  public static function displayItemVariety($type, $styles = array('cobbler', 'bib')){
      $db = new DbLib();
      
      foreach($styles as $idx => $style){
          $ids = $db->getItemGrpIds($type, $style);
          $item = $db->getItem($ids[0], true, false, true);
          $page_info = Util::getPageInfo($type, $style);
          
          echo "<div class='single_item_container'>
              <div class='view_more'>View More</div>
                <a href='javascript:changePage(\"{$page_info[0]}\", \"{$page_info[1]}\");'>
                 <img src='{$item['imgs'][0]['loc']}' alt='" . Util::ucWord($item['style']) . " " . 
                    Util::ucWord($item['prim_color']) . " " .  ($item['sec_color'] != null ? " and " . 
                    Util::ucWord($item['sec_color']) : "") . " " . Util::ucWord($item['pattern']) .
                    " w/ " . Util::ucWord($item['trim_style']) . " " .
                    Util::ucWord($item['trim_prim_color']) . ($item['trim_sec_color'] != null ? " and " . 
                    Util::ucWord($item['trim_sec_color']) : "") . " " . Util::ucWord($item['trim_pattern']) .
                  " Trim Apron" . "'/>
                 <div style='text-align: center;'>
                  {$page_info[2]}
                 </div>
                </a>
               </div>";
      } 
  }
}
?>
