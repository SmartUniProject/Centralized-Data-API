<?php
/**
 * Created by PhpStorm.
 * User: Shalitha Suranga
 * Date: 4/19/2017
 * Time: 8:38 PM
 */


 define("DB_HOST","localhost");
 define("DB_USER","root");
 define("DB_PASSWORD","");
 define("DB_NAME","smartuni");
 define("AP_TOKEN","dev20");


 $questions=array(
     "q1" => "Q1. The course fulfilled the objectives set out in the brochure? ",
     "q2" => "Q2. The course fulfilled the objectives set out in the brochure? ",
     "q3" => "Q3. The course fulfilled the objectives set out in the brochure? ",
     "q4" => "Q4. The course fulfilled the objectives set out in the brochure? ",
     "q5" => "Q5. The course fulfilled the objectives set out in the brochure? ",
     "q6" => "Q6. The course fulfilled the objectives set out in the brochure? ",
     "q7" => "Q7. The course fulfilled the objectives set out in the brochure? ",
     "q8" => "Q8. The course fulfilled the objectives set out in the brochure? ",
     "q9" => "Q9. The course fulfilled the objectives set out in the brochure? ",
     "q10" => "Q10. The course fulfilled the objectives set out in the brochure? "
 );

date_default_timezone_set("Asia/Colombo");

require(__DIR__."/classes/MySQLConnection.php");
require(__DIR__."/classes/Notification.php");
$db=MySQLConnection::getConnection(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);