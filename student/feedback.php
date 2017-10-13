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




        /* ---------- get active feedback node ------------ */
        if(isset($_GET["getactivefeedback"])) {
            $student_id=$db->real_escape_string($_GET["student"]);


            $result=$db->query("SELECT * FROM sm_student WHERE id='$student_id' LIMIT 1;");
            $node=$result->fetch_assoc();
            $year=$node["cyear"];
            $semester=$node["csemester"];
            $degree_id=$node["deg_id"];

            $result = $db->query("
                SELECT sm_feedbackform.*,
                TIMESTAMPDIFF(SECOND,'".date("Y-m-d H:i:s")."', DATE_ADD(sm_feedbackform.added_time,INTERVAL sm_feedbackform.active_minutes MINUTE)) AS seconds
                 
                 FROM
                sm_feedbackform
                INNER JOIN sm_course ON sm_course.id=sm_feedbackform.course_id AND sm_course.deg_id='$degree_id' AND sm_course.cyear='$year' AND sm_course.csemester='$semester'
                
                WHERE DATE_ADD(sm_feedbackform.added_time,INTERVAL sm_feedbackform.active_minutes MINUTE)>='".date("Y-m-d H:i:s")."'
                
                ORDER BY sm_feedbackform.added_time DESC
                
                LIMIT 1
            
            ");



            if ($result->num_rows>0) {
                $info=array();



                while($node=$result->fetch_assoc()) $info[]=$node;

                /*------- check studnt already submitted ------ */
                $result2=$db->query("SELECT id FROM sm_feedbackscore WHERE feedbackform_id=".$info[0]["id"]." AND student_id='$student_id' LIMIT 1;");
                $submitted=$result2->num_rows>0? true:false;


                echo(json_encode(array(
                    "result"=>$info,
                    "questions"=>$questions,
                    "submitted"=>$submitted
                )));
            } else {
                echo(json_encode(array(
                    "error" => "notfound"
                )));
            }
        }

        /* ---------- get active feedback node ------------ */
        if(isset($_GET["getfeedbackresults"])) {
            $lecturer_id=$db->real_escape_string($_GET["lecturer"]);
            $id=$db->real_escape_string($_GET["id"]);

            $result = $db->query("
                 SELECT 
                sm_feedbackform.id,
                TIMESTAMPDIFF(SECOND,'".date("Y-m-d H:i:s")."', DATE_ADD(sm_feedbackform.added_time,INTERVAL sm_feedbackform.active_minutes MINUTE)) AS seconds,
                IFNULL(ROUND(AVG(sm_feedbackscore.q1)/5*100,2) ,0) AS q1,
                IFNULL(ROUND(AVG(sm_feedbackscore.q2)/5*100,2) ,0) AS q2,
                IFNULL(ROUND(AVG(sm_feedbackscore.q3)/5*100,2) ,0) AS q3,
                IFNULL(ROUND(AVG(sm_feedbackscore.q4)/5*100,2),0) AS q4,
                IFNULL(ROUND(AVG(sm_feedbackscore.q5)/5*100,2),0) AS q5,
                IFNULL(ROUND(AVG(sm_feedbackscore.q6)/5*100,2),0) AS q6,
                IFNULL(ROUND(AVG(sm_feedbackscore.q7)/5*100,2),0) AS q7,
                IFNULL(ROUND(AVG(sm_feedbackscore.q8)/5*100,2),0) AS q8,
                IFNULL(ROUND(AVG(sm_feedbackscore.q9)/5*100,2),0) AS q9,
                IFNULL(ROUND(AVG(sm_feedbackscore.q10)/5*100,2),0) AS q10
                 
                FROM
                sm_feedbackform
                INNER JOIN sm_lecturing ON sm_lecturing.course_id=sm_feedbackform.course_id AND sm_lecturing.lecturer_id='$lecturer_id'
                LEFT JOIN sm_feedbackscore ON sm_feedbackscore.feedbackform_id=sm_feedbackform.id
                
                
                WHERE sm_feedbackform.id='$id'
                
                GROUP BY sm_feedbackform.id
                
                LIMIT 1
            
            ");


            if ($result->num_rows) {
                $info=array();

                while($node=$result->fetch_assoc()) $info[]=$node;

                echo(json_encode(array(
                    "result" => $info,
                    "questions" => $questions
                )));
            } else {
                echo(json_encode(array(
                    "error" => "notfound"
                )));
            }
        }


        /* ---------- submit form------------ */
        if(isset($_GET["submitfeedbackform"])) {

            $id=$db->real_escape_string($_GET["id"]);
            $student_id=$db->real_escape_string($_GET["student"]);
            $score=$_GET["score"];
            $comment=$db->real_escape_string($_GET["comment"]);

            $result = $db->query("
            
              INSERT INTO sm_feedbackscore(feedbackform_id,added_time,q1,q2,q3,q4,q5,q6,q7,q8,q9,q10,comment,student_id) VALUES($id,'".date("Y-m-d H:i:s")."','$score[0]','$score[1]','$score[2]','$score[3]','$score[4]','$score[5]','$score[6]','$score[7]','$score[8]','$score[9]','$comment','$student_id');
            
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