<?php
/**
 * Created by PhpStorm.
 * User: Shalitha Suranga
 * Date: 4/20/2017
 * Time: 6:51 AM
 */


require("includes/config.php");
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
if(isset($_GET["token"])){
    if($_GET["token"]==AP_TOKEN){
        $lecturer_id=$db->real_escape_string($_GET["lecturer"]);
        $notif=$db->real_escape_string($_GET["notif"]);

        /* -------- get student info --------- */
        $result=$db->query("
        SELECT 
            sm_lecturer.dept_id AS department,
            sm_department.fac_id AS faculty
        FROM
          sm_lecturer
          INNER JOIN sm_department ON sm_department.id=sm_lecturer.dept_id
        
        WHERE sm_lecturer.id='$lecturer_id' LIMIT 1
        
        ");
        $node=$result->fetch_assoc();
        $dep_id=$node["department"];
        $fac_id=$node["faculty"];
        //var_dump($node);


        $result=$db->query("SELECT COUNT(id) AS messagecount FROM sm_message WHERE to_user='$lecturer_id' AND is_open=0;");
        $node=$result->fetch_assoc();
        $messagecount=$node["messagecount"];

        $result=$db->query("SELECT * FROM sm_notification WHERE type IN(4,5,6) AND id>'$notif' ORDER BY id DESC;");

        $notifications=array();
        while($node=$result->fetch_assoc()){

            /* ------- filtering ------ */
            if($node["type"]=="4") {
                $notifications[] = $node;
            }
            else if($node["type"]=="5") {
                if($node["type_id"]==$fac_id)
                    $notifications[] = $node;
            }
            else if($node["type"]=="6") {
                if($node["type_id"]==$dep_id)
                    $notifications[] = $node;
            }
        }


        echo(json_encode(array(
            "messagecount" => $messagecount,
            "notificationcount" => sizeof($notifications),
            "notifications" => $notifications
        )));
    }
}