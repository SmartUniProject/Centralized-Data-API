<?php
/**
 * Created by PhpStorm.
 * User: Shalitha Suranga
 * Date: 12/27/2015
 * Time: 6:27 PM
 */

class MySQLConnection {

    public $db=null;

    /* ------ constructor ---------- */
    private function __construct($host, $usernname ,$password, $database){
        $this->db=mysqli_connect($host, $usernname, $password, $database) or die("Mysql connection error is occured.");
        $this->db->set_charset("utf8mb4");
    }

    public static function getConnection($host, $usernname ,$password, $database){
        $con=new MySQLConnection($host, $usernname ,$password, $database);
		return $con->db;
    }



} 