<?php
class SpecialDiscount{
  private $name;
  private $start;
  private $end;
  private $amount;
  private $description;
  private $qty;
  
  public function __construct($init){
    $this->name = $init["name"];
    $this->start = $init["start"];
    $this->end = $init["end"];
    $this->amount = $init["amount"];
    $this->percent = $init["percent"];
    $this->description = $init["description"];
    $this->qty = $init["qty"];
  }
  
  public function isActive(){
    $today = date("Y-m-d");
    
    return strtotime($this->start) <= strtotime($today) && strtotime($today) <= strtotime($this->end);
  }
  
  public function getQty(){
    return $this->qty;
  }  
}
?>