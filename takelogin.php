<?php
//print_r($_POST);exit();
require_once("include/bittorrent.php");

if (!mkglobal("username:password"))
	die();
	
session_start();

dbconn();

function bark($text = "Username or password incorrect")
{
  stderr("Login failed!", $text);
}

$res = mysql_query("SELECT id, passhash, secret, enabled,status FROM users WHERE username = " . sqlesc($username) . "");
$row = mysql_fetch_assoc($res);

if (!$row)
	bark();

if ($row["passhash"] != md5($row["secret"] . $password . $row["secret"]))
	bark();

if($row["status"]=="pending")
	bark('You have not confirmed your email address yet. More information is <a href="faq.php#user1">here</a>.');

if ($row["enabled"] == "no")
	bark("This account has been disabled.");

logincookie($row["id"], $row["passhash"]);

if (!empty($_POST["returnto"]))
	header("Location: $_POST[returnto]");
else
	header("Location: browse.php");

?>