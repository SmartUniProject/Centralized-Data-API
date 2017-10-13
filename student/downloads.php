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

        /* ----------  load courses ----------- */
        if(isset($_GET["getcourses"])) {
            $student_id=$db->real_escape_string($_GET["student"]);

            $result=$db->query("SELECT * FROM sm_student WHERE id='$student_id' LIMIT 1;");

            $node=$result->fetch_assoc();
            $degree_id=$node["deg_id"];
            $year=$node["cyear"];
            $semester=$node["csemester"];




            $result = $db->query("
            SELECT 
                  sm_course.*
            FROM          
                  sm_course
             WHERE
                  sm_course.deg_id='$degree_id' 
                  AND sm_course.cyear='$year'
                  AND sm_course.csemester='$semester'
                  
             ORDER BY id ASC     

            
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
            $limit=5;
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






    }
}