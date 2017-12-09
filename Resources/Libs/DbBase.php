<?php
  class DbBase extends mysqli {
    private $dbUser = "<User>";
    private $dbPass = "<Password>";
    private $dbHost = "<DBHost>";
    private $dbName = "<DBName>";

    // The clone and wakeup methods prevents external instantiation of copies of the Singleton class,
    // thus eliminating the possibility of duplicate objects.
    public function __clone() {
      trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
    
    public function __wakeup() {
      trigger_error('Deserializing is not allowed.', E_USER_ERROR);
    }
    
    public function __construct() {
      parent::__construct($this->dbHost, $this->dbUser, $this->dbPass, $this->dbName);
      
      if (mysqli_connect_error()) {
        exit('Connect Error (' . mysqli_connect_errno() . ') '
                . mysqli_connect_error());    
      }
      
      parent::set_charset('utf-8');
    }
    
    // Return $value quoted appropriately for its type.
    public function quote($value){
      // No need to quote numeric types or boolean.
      if(gettype($value) == 'boolean' || gettype($value) == 'integer' || gettype($value) == 'double'){
        return $value;
      }
      
      if(gettype($value) == 'NULL'){
        return 'IS NULL';
      }
      
      // Recursively quote array values.
      if(gettype($value) == 'array'){
        $result = ' IN (';
        foreach($value as $v){
          $result .= $this->quote($v) . ', ';
        }
        
        return substr($result, 0, strlen($result) - 2) . ')';
      }
      
      // Objects cannot be quoted.
      if(gettype($value) == 'object' || gettype($value) == 'resource'){
        return gettype($value);
      }
      
      if(gettype($value) == 'string'){
        return "'{$value}'";
      }
      
      // Parameter has invalid type.
      return gettype($value);
    }
  }
?>