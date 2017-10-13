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




        /* ---------------  list degrees ------------ */
        if(isset($_GET["getmessagethreads"])) {

            $lecturer_id=$db->real_escape_string($_GET["lecturer"]);

            $result = $db->query("
            SELECT 
              COUNT(sm_message.id)  AS messagecount,
              CONCAT(sm_student.firstname,' ',sm_student.lastname) AS studentname,
              sm_student.id
            
            FROM
              sm_message
              INNER JOIN sm_student ON sm_message.from_user=sm_student.id
              
             WHERE sm_message.to_user=$lecturer_id 
               
             GROUP BY sm_message.from_user  
             
             ORDER BY messagecount DESC
                  
            
            ");


            $result2 = $db->query("
            SELECT 
              COUNT(sm_message.id)  AS messagecount,
              sm_student.id
            
            FROM
              sm_message
              INNER JOIN sm_student ON sm_message.from_user=sm_student.id
              
             WHERE sm_message.to_user=$lecturer_id  AND sm_message.is_open='0'
               
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
              
             WHERE ((sm_message.from_user=$lecturer_id AND sm_message.to_user='$student_id')
             OR (sm_message.to_user=$lecturer_id AND sm_message.from_user='$student_id')) 
              
               
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
                $db->query("UPDATE sm_message SET is_open=1 WHERE id IN($ids) AND ((sm_message.from_user=$lecturer_id AND sm_message.to_user='$student_id')
             OR (sm_message.to_user=$lecturer_id AND sm_message.from_user='$student_id')) AND
              
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
              (sm_message.to_user=$lecturer_id AND sm_message.from_user='$student_id') AND
              
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
                (sm_message.to_user=$lecturer_id AND sm_message.from_user='$student_id') AND
              
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
                  VALUES('".date("Y-m-d H:i:s")."','$lecturer_id','$student_id','$message',0);
            
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