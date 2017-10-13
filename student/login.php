<?php
/**
 * Created by PhpStorm.
 * User: Shalitha Suranga
 * Date: 4/20/2017
 * Time: 6:51 AM
 */


require("../includes/config.php");
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
if(isset($_GET["token"])){
    if($_GET["token"]==AP_TOKEN){
        $result=$db->query("SELECT * FROM sm_student WHERE activation='".$db->real_escape_string($_GET["activation"])."'");
        if($result->num_rows>0){
            $node=$result->fetch_assoc();
            /*--------- update database --------*/
            $db->query("UPDATE sm_student SET appinstalled=1 WHERE id='$node[id]'");
            echo(json_encode($node));
        }
        else{
            echo(json_encode(array(
				"error" => "notfound"
			)));
        }
    }
}