<?php

require_once "include/bittorrent.php";
require_once "include/user_functions.php";

$id = $_GET["id"];
//$id = (int)$_GET["id"];
$md5 = $_GET["secret"];

if (!is_valid_id($id))
	httperr();

dbconn();


$res = mysql_query("SELECT passhash, editsecret, status FROM users WHERE id = $id");
$row = mysql_fetch_assoc($res);

if (!$row)
	httperr();

if ($row["status"] != "pending") {
	header("Refresh: 0; url=$BASEURL/ok.php?type=confirmed");
	exit();
}

$sec = hash_pad($row["editsecret"]);
if ($md5 != md5($sec))
	httperr();

mysql_query("UPDATE users SET status='confirmed', editsecret='' WHERE id=$id AND status='pending'");

if (!mysql_affected_rows())
	httperr();

logincookie($id, $row["passhash"]);

header("Refresh: 0; url=$BASEURL/ok.php?type=confirm");

?>