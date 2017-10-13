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

        if(isset($_GET["gettimetable"])) {

            $lecturer_id=$db->real_escape_string($_GET["lecturer"]);
            $day=$db->real_escape_string($_GET["day"]);


            $result = $db->query("
                SELECT 
                
                sm_timeslot.*,sm_course.name AS coursename,sm_hall.name AS hallname,sm_course.cyear, sm_course.csemester
                
                FROM sm_timeslot 
                                          INNER JOIN sm_lecturing ON sm_lecturing.course_id=sm_timeslot.course_id
                                          INNER JOIN sm_course ON sm_timeslot.course_id=sm_course.id
                                          INNER JOIN sm_hall ON sm_hall.id=sm_timeslot.hall_id
                                          WHERE sm_lecturing.lecturer_id=$lecturer_id
                                          AND sm_timeslot.slot_date='$day'
                                          
                                          
            ");
            if ($result->num_rows) {
                $info=array();
                while($node=$result->fetch_assoc()) $info[]=$node;

                echo(json_encode($info));
            } else {
                echo(json_encode(array(
                    "error" => "nodata"
                )));
            }


        }

        /* ----------  load extralecture info ----------- */
        if(isset($_GET["getextralectureinfo"])) {

            $lecturer_id=$db->real_escape_string($_GET["lecturer"]);
            $info1=array();
            $info2=array();

            $result = $db->query("
            SELECT 
              sm_hall.*
            FROM 
              sm_hall
            ORDER BY sm_hall.name ASC  
            ");

            while($node=$result->fetch_assoc()) $info1[]=$node;




            $result = $db->query("
            SELECT 
              sm_course.*
            FROM 
            
                  sm_lecturing
                  INNER JOIN sm_course ON sm_course.id=sm_lecturing.course_id AND sm_lecturing.lecturer_id=$lecturer_id
  
             GROUP BY sm_course.id    
                  
            
            ");
            while($node=$result->fetch_assoc()) $info2[]=$node;


            echo(json_encode(array(
                "halls" => $info1,
                "courses" => $info2,
            )));
        }



        if(isset($_GET["addextralecture"])) {

            $lecturer_id=$db->real_escape_string($_GET["lecturer"]);
            $course_id=$db->real_escape_string($_GET["course"]);
            $hall_id=$db->real_escape_string($_GET["hall"]);
            $day=$db->real_escape_string($_GET["day"]);
            $start=preg_replace("/( AM)|( PM)/","",$db->real_escape_string($_GET["start"]));
            $end=preg_replace("/( AM)|( PM)/","",$db->real_escape_string($_GET["end"]));


            $result = $db->query("
                SELECT sm_lecturing.lecturer_id 
                FROM 
                sm_timeslot 
                INNER JOIN sm_lecturing ON sm_lecturing.course_id=sm_timeslot.course_id 
                WHERE sm_timeslot.slot_date='$day' AND ((sm_timeslot.start_time<='$start' AND sm_timeslot.start_time>'$start') OR (sm_timeslot.start_time<'$end' AND sm_timeslot.end_time>='$end') OR (sm_timeslot.start_time>='$start' AND sm_timeslot.end_time<='$end') OR (sm_timeslot.start_time>='$start' AND sm_timeslot.end_time<='$end')) AND sm_lecturing.lecturer_id=$lecturer_id
                ;                             
            ");
            if ($result->num_rows==0) {
                $result2 = $db->query("
                SELECT sm_timeslot.id 
                FROM sm_timeslot 
                WHERE 
                sm_timeslot.slot_date='$day' AND ((sm_timeslot.start_time<'$start' AND sm_timeslot.end_time>'$start') OR (sm_timeslot.start_time<'$end' AND sm_timeslot.end_time>'$end') OR (sm_timeslot.start_time>='$start' AND sm_timeslot.end_time<='$end')) AND sm_timeslot.hall_id=$hall_id;                           
                ;");

                if($result2->num_rows==0){
                    $db->query("INSERT INTO sm_timeslot(course_id,hall_id,slot_date,start_time,end_time,is_extra) VALUES('$course_id','$hall_id','$day','$start','$end','1');");

                    $result=$db->query("SELECT * FROM sm_course WHERE id='$course_id' LIMIT 1;");
                    $node=$result->fetch_assoc();
                    /* -------- add notification ----- */
                    $notification=new Notification();
                    $notification->setMessage("Extra lecture of $node[name] ($node[id]) is added.",8);
                    $notification->setTypeId($node["id"]);
                    $notification->setTypeOpt($node["cyear"]);
                    $notification->sendNotification();

                    echo(json_encode(array(
                        "success" => $db->insert_id
                    )));
                }
                else{
                    echo(json_encode(array(
                        "error" => "hallbusy"
                    )));
                }

            }
            else {
                echo(json_encode(array(
                    "error" => "notime"
                )));
            }


        }



        if(isset($_GET["removeextralecture"])) {

            $id=$db->real_escape_string($_GET["removeextralecture"]);
            $result=$db->query("SELECT sm_course.*,sm_timeslot.slot_date AS day FROM sm_timeslot LEFT JOIN sm_course ON sm_course.id=sm_timeslot.course_id WHERE sm_timeslot.id='$id' LIMIT 1;");
            $node=$result->fetch_assoc();

            $result = $db->query("
                DELETE FROM sm_timeslot WHERE id=$id AND is_extra='1' LIMIT 1;                         
            ");
            if ($result) {


                /* -------- add notification ----- */
                $notification=new Notification();
                $notification->setMessage("$node[day] extra lecture of $node[name] ($node[id]) was removed.",8);
                $notification->setTypeId($node["id"]);
                $notification->setTypeOpt($node["cyear"]);
                $notification->sendNotification();

                echo(json_encode(array(
                    "success" => $id
                )));
            } else {
                echo(json_encode(array(
                    "error" => "nodata"
                )));
            }


        }

        if(isset($_GET["gethallfreeslots"])) {

            $hall_id=$db->real_escape_string($_GET["hall"]);
            $day=$db->real_escape_string($_GET["day"]);


            $result = $db->query("
                SELECT * FROM sm_timeslot WHERE slot_date='$day' AND hall_id='$hall_id'                        
            ");
            $startTime=mktime(6,0,0);
            $endTime=mktime(20,30,0);
            $availTimes=array();
            $availTimesx=array();

            if ($result->num_rows>0) {
                while($node=$result->fetch_assoc()){
                    $s_date=strtotime($node["start_time"]);
                    $slot_start=mktime(date("H",$s_date),date("i",$s_date),date("s",$s_date));
                    $e_date=strtotime($node["end_time"]);
                    $slot_end=mktime(date("H",$e_date),date("i",$e_date),date("s",$e_date));
                    $availTimes[]=array(date("H:i",$startTime),date("H:i",$slot_start));
                    $startTime=$slot_end;
                }


            } else {

            }

            $availTimes[]=array(date("H:i",$startTime),date("H:i",$endTime));
            foreach($availTimes as $item){
                if($item[0]!=$item[1]){
                    $availTimesx[]=$item;
                }
            }


            echo(json_encode(array(
                "freeslots" => $availTimesx
            )));


        }


    }
}