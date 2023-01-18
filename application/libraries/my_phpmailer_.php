<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class My_PHPMailer {
    public function My_PHPMailer() {
        require_once('PHPMailer/src/Exception.php');
        require_once('PHPMailer/src/PHPMailer.php');
        require_once('PHPMailer/src/SMTP.php');

    }
}

?>