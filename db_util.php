<?php
class db {
  private $url = "sql.njit.edu"
  private $user = "ucid"
  private $pass = "pass"
  private $db_name = "db_name"
  private $pdo_conn = null;
  
  public function connect(){
    try {
      $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ];
      $dsn = sprintf("mysql:host=%s;dbname=%s", $url, $db_name);
      $this->pdo_conn = new PDO($dsn, $this->user, $this->pass, $options);
    }
    catch (PDOException $e){
      echo sprintf('Error connecting: %s',$e->getMessage());
      return null;
    }
    return $this->pdo_conn;
  }
  
  public function check_credentials($user, $pass){
    $fail_string = "Unsuccessful login with UCID(%s)."
    $success_string "Successful login with UCID(%s)."
    $ret = array(
      "database" => "",
      "webnjit" => array(
        "success" : true,
        "message" : ""
      )
    );
    return $ret
  }
}