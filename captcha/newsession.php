<?php

// Include the random string file
//require 'rand.php';
$str = '';
	for($i=0; $i<6; $i++){
$str .= chr(rand(0,25)+65);
}

// Begin a new session
session_start();

// Set the session contents
$_SESSION['captcha_id'] = $str;

?>