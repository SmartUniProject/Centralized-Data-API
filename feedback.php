<?php
/**
 * Created by PhpStorm.
 * User: Shalitha Suranga
 * Date: 4/20/2017
 * Time: 6:51 AM
 */


require("includes/config.php");
require("includes/dompdf-master/dompdf_config.inc.php");
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


        /* ---------- get active feedback node ------------ */
        if(isset($_GET["getactivefeedback"])) {
            $lecturer_id=$db->real_escape_string($_GET["lecturer"]);

            $result = $db->query("
                SELECT sm_feedbackform.* FROM
                sm_feedbackform
                INNER JOIN sm_lecturing ON sm_lecturing.course_id=sm_feedbackform.course_id AND sm_lecturing.lecturer_id='$lecturer_id'
                
                WHERE DATE_ADD(sm_feedbackform.added_time,INTERVAL sm_feedbackform.active_minutes MINUTE)>='".date("Y-m-d H:i:s")."'
                
                ORDER BY sm_feedbackform.added_time DESC
                
                LIMIT 1
            
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

        /* ---------- get active feedback node ------------ */
        if(isset($_GET["getfeedbackresults"])) {
            $lecturer_id=$db->real_escape_string($_GET["lecturer"]);
            $id=$db->real_escape_string($_GET["id"]);

            $result = $db->query("
                 SELECT 
                sm_feedbackform.*,
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


        /* ---------- add form------------ */
        if(isset($_GET["addfeedbackform"])) {

            $course_id=$db->real_escape_string($_GET["course"]);
            $minutes=$db->real_escape_string($_GET["activeminutes"]);

            $result = $db->query("
            
              INSERT INTO sm_feedbackform(course_id,added_time,active_minutes ) VALUES('$course_id','".date("Y-m-d H:i:s")."','$minutes');
            
            ");
            if ($result) {
                $result=$db->query("SELECT * FROM sm_course WHERE id='$course_id' LIMIT 1;");
                $node=$result->fetch_assoc();
                /* -------- add notification ----- */
                $notification=new Notification();
                $notification->setMessage("Feed back form added $node[name] ($node[id]).",10);
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


        /* ---------- get active feedback node ------------ */
        if(isset($_GET["getfeedbackformdoc"])) {
            $id=$db->real_escape_string($_GET["id"]);

            $result = $db->query("
                 SELECT 
                sm_feedbackform.*,
                CONCAT(sm_lecturer.title,' ',sm_lecturer.firstname, ' ',sm_lecturer.lastname) AS lecturer ,
                sm_course.id AS course,
                sm_course.name AS coursename
                 
                FROM
                sm_feedbackform
                INNER JOIN sm_course ON sm_course.id=sm_feedbackform.course_id 
                INNER JOIN sm_lecturing ON sm_lecturing.course_id=sm_feedbackform.course_id
                INNER JOIN sm_lecturer ON sm_lecturing.lecturer_id=sm_lecturer.id
                
                
                WHERE sm_feedbackform.id='$id'
                
                GROUP BY sm_feedbackform.id
                
                LIMIT 1
            
            ");

            $result2 = $db->query("
                 SELECT 
                sm_feedbackform.*,
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
                LEFT JOIN sm_feedbackscore ON sm_feedbackscore.feedbackform_id=sm_feedbackform.id
                
                
                WHERE sm_feedbackform.id='$id'
                
                GROUP BY sm_feedbackform.id
                
                LIMIT 1
            
            ");


          /*  $result3 = $db->query("
                 SELECT 
                sm_feedbackscore.comment
                 
                FROM
                sm_feedbackscore
                
                WHERE sm_feedbackscore.feedbackform_id='$id'
                
            ");


*/

            if ($result->num_rows>0 && $result2->num_rows>0) {
                $node=$result->fetch_assoc();
                $node2=$result2->fetch_assoc();
                $con="";
                $con.="<h2>$node[course] $node[coursename]</h2>";
                $con.="<h4>Lecturer : $node[lecturer] - Feedback form</h4>";

                $con.="<ol>";
                $total=0;
                for($i=1; $i<=sizeof($questions); $i++){
                    $con.="<li style='border: solid 1px #222222; padding:6px;'>".($questions["q$i"])."<span style='text-align:right;'>".$node2["q$i"]."%</span></li>";
                    $total+=$node2["q$i"];
                }
                $total/=10;

                $con."</ol>";

               /* $con.="<ul>";
                $con.="<h4>Comments(".$result3->num_rows.")</h4>";
                while($node3=$result3->fetch_assoc()){
                    $con.="<li style='border: solid 1px #222222; padding:6px;'>$node3[comment]</li>";
                }
                $con.="</ul>";*/

                $con.="<br/><br/><hr/><span>Generated by SmartUni.</span>";
                $dompdf = new DOMPDF();
                $dompdf->load_html($con);
                $dompdf->set_paper("A4", "portrait");

                $dompdf->render();

                $pdfcontent=$dompdf->output();
                //$filename="files/Groups-$node[course]-Feedback.pdf";

                //file_put_contents($filename,$pdfcontent);

                echo($pdfcontent);
            } else {
                echo("error");
            }
        }



        /* ---------- get active feedback node ------------ */
        if(isset($_GET["getlastfeedbackformdoc"])) {

            $lecturer_id=$db->real_escape_string($_GET["lecturer"]);
            $course_id=$db->real_escape_string($_GET["course"]);

            $result = $db->query("
                 SELECT 
                sm_feedbackform.*,
                CONCAT(sm_lecturer.title,' ',sm_lecturer.firstname, ' ',sm_lecturer.lastname) AS lecturer ,
                sm_course.id AS course,
                sm_course.name AS coursename
                 
                FROM
                sm_feedbackform
                INNER JOIN sm_course ON sm_course.id=sm_feedbackform.course_id AND sm_course.id='$course_id'
                INNER JOIN sm_lecturing ON sm_lecturing.course_id=sm_feedbackform.course_id
                INNER JOIN sm_lecturer ON sm_lecturing.lecturer_id=sm_lecturer.id AND sm_lecturer.id='$lecturer_id'
                
                
                WHERE DATE_ADD(sm_feedbackform.added_time,INTERVAL sm_feedbackform.active_minutes MINUTE)<'".date("Y-m-d H:i:s")."'
                
                
                GROUP BY sm_feedbackform.id
                
                ORDER BY sm_feedbackform.id DESC
                
                LIMIT 1
            
            ");


            if($result->num_rows>0) {
                $node=$result->fetch_assoc();
                $result2 = $db->query("
                 SELECT 
                sm_feedbackform.*,
                TIMESTAMPDIFF(SECOND,'" . date("Y-m-d H:i:s") . "', DATE_ADD(sm_feedbackform.added_time,INTERVAL sm_feedbackform.active_minutes MINUTE)) AS seconds,
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
                LEFT JOIN sm_feedbackscore ON sm_feedbackscore.feedbackform_id=sm_feedbackform.id
                
                
                WHERE sm_feedbackform.id='$node[id]'
                
                GROUP BY sm_feedbackform.id
                
                LIMIT 1
            
            ");


                $result3 = $db->query("
                 SELECT 
                sm_feedbackscore.comment
                 
                FROM
                sm_feedbackscore
                
                WHERE sm_feedbackscore.feedbackform_id='$node[id]' AND sm_feedbackscore.comment<>''
                
            ");

                if ($result2->num_rows > 0) {

                    $node2 = $result2->fetch_assoc();
                    $con = "";
                    $con .= "<h2>$node[course] $node[coursename]</h2>";
                    $con .= "<h4>Lecturer : $node[lecturer] - Feedback form</h4>";

                    $con .= "<ol>";
                    $total = 0;
                    for ($i = 1; $i <= sizeof($questions); $i++) {
                        $con .= "<li style='border: solid 1px #222222; padding:6px;'>" . ($questions["q$i"]) . "<span style='text-align:right;'>" . $node2["q$i"] . "%</span></li>";
                        $total += $node2["q$i"];
                    }
                    $total /= 10;

                    $con . "</ol>";

                    $con .= "<ul>";
                    $con .= "<h4>Comments(" . $result3->num_rows . ")</h4>";
                    while ($node3 = $result3->fetch_assoc()) {
                        $con .= "<li style='border: solid 1px #222222; padding:6px;'>$node3[comment]</li>";
                    }
                    $con .= "</ul>";

                    $con .= "<br/><br/><hr/><span>Generated by SmartUni.</span>";
                    $dompdf = new DOMPDF();
                    $dompdf->load_html($con);
                    $dompdf->set_paper("A4", "portrait");

                    $dompdf->render();

                    $pdfcontent = $dompdf->output();
                    $filename = "files/Groups-$node[course]-Feedback.pdf";

                    file_put_contents($filename, $pdfcontent);

                    echo(json_encode(array(
                        "file" => $filename
                    )));
                } else {
                    echo(json_encode(array(
                        "error" => "notfound"
                    )));
                }
            }
        }




    }
}