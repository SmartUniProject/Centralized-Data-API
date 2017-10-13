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

        /* ----------  load courses ----------- */
        if(isset($_GET["getcourses"])) {
            $lecturer_id=$db->real_escape_string($_GET["lecturer"]);
            $result = $db->query("
            SELECT 
              sm_course.*
            FROM 
            

                  sm_lecturing 
                  INNER JOIN sm_course ON sm_course.id=sm_lecturing.course_id
                  
                  
             WHERE
                  
                  sm_lecturing.lecturer_id=$lecturer_id
                  
             GROUP BY sm_course.id    
                  
            
            ");
            if ($result->num_rows) {
                $info=array();

                while($node=$result->fetch_assoc()) $info[]=$node;

                echo(json_encode($info));
            } else {
                echo(json_encode(array(
                    "error" => "notfound"
                )));
            }
        }


        /* ---------- get downloads ------------ */
        if(isset($_GET["getdownloads"])) {
            $course_id=$db->real_escape_string($_GET["course"]);
            $limit=3;
            $offset=$db->real_escape_string($_GET["offset"]);

            $result = $db->query("
            SELECT 
              sm_lecturematerial.*
            FROM 
              sm_lecturematerial
            WHERE
              sm_lecturematerial.course_id='$course_id'
            
            ORDER BY sm_lecturematerial.added_time DESC    
            
            LIMIT $limit OFFSET $offset
            
            ");
            if ($result->num_rows) {
                $info=array();

                while($node=$result->fetch_assoc()) $info[]=$node;

                echo(json_encode($info));
            } else {
                echo(json_encode(array(
                    "error" => "notfound"
                )));
            }
        }


        /* ---------- add new download ------------ */
        if(isset($_GET["adddownload"])) {

            $course_id=$db->real_escape_string($_GET["course"]);
            $note=$db->real_escape_string($_GET["note"]);
            $link=$db->real_escape_string($_GET["link"]);
            $link=base64_decode($link);

            $result = $db->query("
            
              INSERT INTO sm_lecturematerial(course_id,added_time,description,downloadlink ) VALUES('$course_id','".date("Y-m-d H:i:s")."','$note','$link');
            
            ");
            if ($result) {
                $result=$db->query("SELECT * FROM sm_course WHERE id='$course_id' LIMIT 1;");
                $node=$result->fetch_assoc();
                /* -------- add notification ----- */
                $notification=new Notification();
                $notification->setMessage("New lecture materials were added to $node[name] ($node[id]).",7);
                $notification->setTypeId($node["id"]);
                $notification->setTypeOpt($node["cyear"]);
                $notification->sendNotification();

                echo(json_encode(array(
                    "success" => $db->insert_id
                )));
            } else {
                echo(json_encode(array(
                    "error" => "notfound"
                )));
            }
        }

        /* ---------- add new download ------------ */
        if(isset($_GET["removedownload"])) {

            $id=$db->real_escape_string($_GET["removedownload"]);

            $result = $db->query("DELETE FROM sm_lecturematerial WHERE id=$id");
            if ($result) {
                echo(json_encode(array(
                    "success" => $id
                )));
            } else {
                echo(json_encode(array(
                    "error" => "notfound"
                )));
            }
        }




    }
}