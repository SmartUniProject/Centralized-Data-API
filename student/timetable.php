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

        if(isset($_GET["gettimetable"])) {

            $student_id=$db->real_escape_string($_GET["student"]);
            $day=$db->real_escape_string($_GET["day"]);


            $result=$db->query("SELECT * FROM sm_student WHERE id='$student_id' LIMIT 1;");
            $node=$result->fetch_assoc();
            $year=$node["cyear"];
            $semester=$node["csemester"];
            $degree_id=$node["deg_id"];


            $result = $db->query("
                SELECT 
                
                sm_timeslot.*,sm_course.name AS coursename,sm_hall.name AS hallname,
                GROUP_CONCAT(CONCAT(sm_lecturer.id,'-',sm_lecturer.title,' ',sm_lecturer.firstname,' ',sm_lecturer.lastname)) AS lecturers
                
                FROM sm_timeslot 
                                          
                                          INNER JOIN sm_course ON sm_timeslot.course_id=sm_course.id
                                          INNER JOIN sm_hall ON sm_hall.id=sm_timeslot.hall_id
                                          INNER JOIN sm_lecturing ON sm_lecturing.course_id=sm_timeslot.course_id
                                          INNER JOIN sm_lecturer ON sm_lecturer.id=sm_lecturing.lecturer_id
                                          
                                          WHERE sm_course.deg_id=$degree_id AND sm_course.cyear=$year AND sm_course.csemester=$semester
                                          
                                          AND sm_timeslot.slot_date='$day'
                                          
                                          
                                          GROUP BY sm_timeslot.id
                                          
                                          
                                          
                                          
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







    }
}