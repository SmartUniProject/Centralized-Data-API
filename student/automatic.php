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
        $student_id=$db->real_escape_string($_GET["student"]);
        $notif=$db->real_escape_string($_GET["notif"]);

        /* -------- get student info --------- */
        $result=$db->query("
        SELECT 
            sm_student.deg_id AS degree,
            sm_student.cyear,
            sm_degree.dep_id AS department,
            sm_degree.fac_id AS faculty
        FROM
          sm_student
          INNER JOIN sm_degree ON sm_degree.id=sm_student.deg_id
        
        WHERE sm_student.id='$student_id' LIMIT 1
        
        ");
        $node=$result->fetch_assoc();
        $degree_id=$node["degree"];
        $dep_id=$node["department"];
        $fac_id=$node["faculty"];
        $year=$node["cyear"];
        //var_dump($node);


        $result=$db->query("SELECT COUNT(id) AS messagecount FROM sm_message WHERE to_user='$student_id' AND is_open=0;");
        $node=$result->fetch_assoc();
        $messagecount=$node["messagecount"];

        $result=$db->query("SELECT * FROM sm_notification WHERE type IN(0,1,2,3,7,8,9,10) AND id>'$notif' ORDER BY id DESC;");

        $notifications=array();
        while($node=$result->fetch_assoc()){


            /* ------- filtering ------ */
            if($node["type"]=="0") {
                if($node["type_opt"]==$year || $node["type_opt"]=="-1")
                    $notifications[] = $node;
            }
            else if($node["type"]=="1") {
                if(($node["type_opt"]==$year || $node["type_opt"]=="-1") && $node["type_id"]==$fac_id)
                    $notifications[] = $node;
            }
            else if($node["type"]=="2") {
                if(($node["type_opt"]==$year || $node["type_opt"]=="-1") && $node["type_id"]==$dep_id)
                    $notifications[] = $node;
            }
            else if($node["type"]=="2") {
                if(($node["type_opt"]==$year || $node["type_opt"]=="-1") && $node["type_id"]==$degree_id)
                    $notifications[] = $node;
            }
            else if($node["type"]=="7" || $node["type"]=="8" || $node["type"]=="9" || $node["type"]=="10") {
                /*---- check course is in the degree and the year of course is equal to student ---- */
                $result_i=$db->query("SELECT id FROM sm_course WHERE id='$node[type_id]' AND deg_id='$degree_id' AND cyear='$node[type_opt]' LIMIT 1;");
                    if($result_i->num_rows>0)
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