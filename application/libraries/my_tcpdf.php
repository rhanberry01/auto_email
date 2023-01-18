<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class My_TCPDF {
    public function My_TCPDF() {
        require_once('tcpdf/tcpdf/tcpdf.php');
    }
}