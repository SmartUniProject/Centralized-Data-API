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




        /* ---------------  list degrees ------------ */
        if(isset($_GET["getmessagethreads"])) {

            $student_id=$db->real_escape_string($_GET["student"]);

            $result = $db->query("
            SELECT 
              COUNT(sm_message.id)  AS messagecount,
              CASE WHEN (sm_message.from_user  >0)  THEN CONCAT(sm_message.from_user,sm_message.to_user) ELSE CONCAT(sm_message.to_user,sm_message.from_user) END AS thread,
              IFNULL(CONCAT(sm_lecturer.title,' ',sm_lecturer.firstname,' ',sm_lecturer.lastname),CONCAT(sm_lecturer2.title,' ',sm_lecturer2.firstname,' ',sm_lecturer2.lastname))  AS lecturername,
              IFNULL(sm_department.name,sm_department2.name) AS deptname,
              IFNULL(sm_lecturer.id,sm_lecturer2.id) AS id
            
            FROM
              sm_message
              LEFT JOIN sm_lecturer ON sm_message.from_user=sm_lecturer.id
              LEFT JOIN sm_lecturer AS sm_lecturer2 ON sm_message.to_user=sm_lecturer2.id
              LEFT JOIN sm_department ON sm_department.id = sm_lecturer.dept_id
              LEFT JOIN sm_department AS sm_department2 ON sm_department2.id = sm_lecturer2.dept_id
              
             WHERE sm_message.to_user='$student_id' OR sm_message.from_user='$student_id'
               
             GROUP BY thread  
             
             ORDER BY messagecount DESC
                  
            
            ");





            $result2 = $db->query("
            SELECT 
              COUNT(sm_message.id)  AS messagecount,
              sm_lecturer.id
            
            FROM
              sm_message
              INNER JOIN sm_lecturer ON sm_message.from_user=sm_lecturer.id
              
             WHERE sm_message.to_user='$student_id'  AND sm_message.is_open='0'
               
             GROUP BY sm_message.from_user  
                  
            
            ");


            $info1=array();
            $info2=array();

            while($node=$result->fetch_assoc()) {
                $info1[]=$node;
            }
            while($node=$result2->fetch_assoc()) {
                $info2[$node["id"]]=$node["messagecount"];
            }

            echo(json_encode(array(
                "total"=>$info1,
                "unread"=>$info2
            )));

        }


        /* ---------------  load message thread ------------ */
        if(isset($_GET["getmessagethread"])) {

            $lecturer_id=$db->real_escape_string($_GET["lecturer"]);
            $student_id=$db->real_escape_string($_GET["student"]);
            $offset=$db->real_escape_string($_GET["offset"]);
            $limit=10;

            $result = $db->query("
            SELECT 
              sm_message.*
            FROM
              sm_message
              
             WHERE ((sm_message.from_user='$student_id' AND sm_message.to_user='$lecturer_id')
             OR (sm_message.to_user='$student_id' AND sm_message.from_user='$lecturer_id')) 
              
               
             ORDER BY id DESC LIMIT $limit OFFSET $offset 
                  
            
            ");
            if ($result->num_rows) {
                $info=array();
                $ids="";
                while($node=$result->fetch_assoc()) {
                    $info[]=$node;
                    $ids.="'$node[id]',";
                }
                $ids=preg_replace("/,$/","",$ids);
                $db->query("UPDATE sm_message SET is_open=1 WHERE id IN($ids) AND ((sm_message.from_user='$student_id' AND sm_message.to_user='$lecturer_id')
             OR (sm_message.to_user=$student_id AND sm_message.from_user='$lecturer_id'))  AND
              
              sm_message.is_open='0'");


                echo(json_encode($info));
            } else {
                echo(json_encode(array(
                    "error" => "notfound"
                )));
            }
        }

        /* ---------------  incoming messages ------------ */
        if(isset($_GET["getmessagethreadincoming"])) {

            $lecturer_id=$db->real_escape_string($_GET["lecturer"]);
            $student_id=$db->real_escape_string($_GET["student"]);
            $timestamp=$db->real_escape_string($_GET["timestamp"]);

            $result = $db->query("
            SELECT 
              sm_message.*
            FROM
              sm_message
              
             WHERE
              (sm_message.from_user='$lecturer_id' AND sm_message.to_user='$student_id') AND
              
              sm_message.is_open='0' AND id>'$timestamp'
               
             ORDER BY id DESC 
                  
            
            ");
            if ($result->num_rows) {
                $info=array();
                $ids="";
                while($node=$result->fetch_assoc()){
                    $info[]=$node;
                    $ids.="'$node[id]',";
                }
                $ids=preg_replace("/,$/","",$ids);
                $db->query("UPDATE sm_message SET is_open=1 WHERE id IN($ids) AND 
                (sm_message.from_user='$lecturer_id' AND sm_message.to_user='$student_id') AND
              
              sm_message.is_open='0'");

                echo(json_encode($info));
            } else {
                echo(json_encode(array(
                    "error" => "notfound"
                )));
            }
        }



        /* ---------------  add message ------------ */
        if(isset($_GET["addmessage"])) {

            $lecturer_id=$db->real_escape_string($_GET["lecturer"]);
            $student_id=$db->real_escape_string($_GET["student"]);
            $message=$db->real_escape_string($_GET["message"]);

            $result = $db->query("
            
            INSERT INTO sm_message(id, from_user,to_user,content,is_open) 
                  VALUES('".date("Y-m-d H:i:s")."','$student_id','$lecturer_id','$message',0);
            
            ");
            if ($result) {
                echo(json_encode(array(
                    "success" => $db->insert_id
                )));
            } else {
                echo(json_encode(array(
                    "error" => "notfound"
                )));
            }
        }







    }
}