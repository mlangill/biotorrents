<?php

require_once("include/secrets.php");
require_once("include/bittorrent.php");
//require_once("include/benc.php");

if (!@mysql_connect($mysql_host, $mysql_user, $mysql_pass))
    {
	  exit();
    }
    @mysql_select_db($mysql_db) or exit();;




function hash_where($name, $hash) {
    $shhash = preg_replace('/ *$/s', "", $hash);
    return "($name = " . sqlesc($hash) . " OR $name = " . sqlesc($shhash) . ")";
}

function benc_str($s) {
	return strlen($s) . ":$s";
}

$r = "d" . benc_str("files") . "d";

$fields = "info_hash, times_completed, seeders, leechers";

if (!isset($_GET["info_hash"]))
	$query = "SELECT $fields FROM torrents ORDER BY info_hash";
else
	$query = "SELECT $fields FROM torrents WHERE " . hash_where("info_hash", unesc($_GET["info_hash"]));

$res = mysql_query($query);

while ($row = mysql_fetch_assoc($res)) {
	$r .= "20:" . str_pad($row["info_hash"], 20) . "d" .
		benc_str("complete") . "i" . $row["seeders"] . "e" .
		benc_str("downloaded") . "i" . $row["times_completed"] . "e" .
		benc_str("incomplete") . "i" . $row["leechers"] . "e" .
		"e";
}

$r .= "ee";

header("Content-Type: text/plain");
print($r);

?>