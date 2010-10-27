<?php

require_once("include/bittorrent.php");

function bark($msg) {
	genbark($msg, "Edit failed!");
}

if (!mkglobal("id:name:descr:type:lic"))
	bark("missing form data");

$id = 0 + $id;
if (!$id)
	die();

dbconn();

loggedinorreturn();

$res = mysql_query("SELECT owner, filename, save_as FROM torrents WHERE id = $id");
$row = mysql_fetch_assoc($res);
if (!$row)
	die();

if ($CURUSER["id"] != $row["owner"] && $CURUSER['class'] < UC_MODERATOR)
	bark("You're not the owner! How did that happen?\n");

$updateset = array();

$fname = $row["filename"];
preg_match('/^(.+)\.torrent$/si', $fname, $matches);
$shortfname = $matches[1];
$dname = $row["save_as"];


$version_action = $_POST['version_action'];
if ($version_action == 'update'){
   $version_id = get_version_id_for_torrent($_POST['version'], $id);
   $updateset[] = "version = ". $version_id;
}else
   if ($version_action == 'remove')
            $updateset[] = 'version = 0';


$updateset[] = "name = " . sqlesc($name);
$updateset[] = "search_text = " . sqlesc(searchfield("$shortfname $dname $name"));
$updateset[] = "descr = " . sqlesc($descr);
$updateset[] = "ori_descr = " . sqlesc($descr);
$updateset[] = "category = " . (0 + $type);
$updateset[] = "license = " . (0 + $lic);
//if ($CURUSER["admin"] == "yes") {
if ($CURUSER['class'] > UC_MODERATOR) {
	if ( isset($_POST["banned"]) ) {
		$updateset[] = "banned = 'yes'";
		$_POST["visible"] = 0;
	}
	else
		$updateset[] = "banned = 'no'";
}
$updateset[] = "visible = '" . ( isset($_POST["visible"]) ? "yes" : "no") . "'";

mysql_query("UPDATE torrents SET " . join(",", $updateset) . " WHERE id = $id");

write_log("Torrent $id ($name) was edited by $CURUSER[username]");

$returl = "details.php?id=$id&edited=1";
if (isset($_POST["returnto"]))
	$returl .= "&returnto=" . urlencode($_POST["returnto"]);
header("Refresh: 0; url=$returl");


?>