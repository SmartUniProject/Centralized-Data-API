<?php

/**
 * Created by PhpStorm.
 * User: Shalitha Suranga
 * Date: 4/26/2017
 * Time: 1:29 PM
 */
class Notification {
    public $message="";
    public $type=-1;
    public $typeId=-1;
    public $typeOpt=-1;

    public function __construct() {

    }

    public function setMessage($message,$type){
        $this->message=$message;
        $this->type=$type;
    }

    public function setTypeId($type){
        $this->typeId=$type;
    }

    public function setTypeOpt($typeOpt){
        $this->typeOpt=$typeOpt;
    }

    public function sendNotification(){
        global $db;
        $db->query("INSERT INTO sm_notification VALUES('".date("Y-m-d H:i:s")."','".$this->type."','".$this->typeId."','".$this->typeOpt."','$this->message');");
    }


}