<?php

require_once("include/bittorrent.php");

dbconn();

loggedinorreturn();



if (!preg_match(':^/(\d{1,10})/(.+)\.torrent$:', $_SERVER["PATH_INFO"], $matches))
	httperr();

$id = 0 + $matches[1];
if (!$id)
	httperr();




$res = mysql_query("SELECT name, filename FROM torrents WHERE id = $id") or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_assoc($res);

$fn = "$torrent_dir/$id.torrent";

if (!$row || !is_file($fn) || !is_readable($fn))
	httperr();


mysql_query("UPDATE torrents SET hits = hits + 1 WHERE id = $id");

require_once "include/benc.php";



if (!isset($CURUSER['passkey']) || strlen($CURUSER['passkey']) != 32) {

$CURUSER['passkey'] = md5($CURUSER['username'].time().$CURUSER['passhash']);

mysql_query("UPDATE users SET passkey='{$CURUSER['passkey']}' WHERE id={$CURUSER['id']}");

}



$dict = bdec_file($fn, filesize($fn));

$dict['value']['announce']['value'] = "{$announce_urls[0]}?passkey={$CURUSER['passkey']}";
$dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];
$dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);

//Added support for announce-list (if set by user's uploaded torrent)
//if(isset($dict['value']['announce-list']['value'][0]['value'][0]['value'])){
//$dict['value']['announce-list']['value'][0]['value'][0]['value'] = $dict['value']['announce']['value'];
//$dict['value']['announce-list']['value'][0]['value'][0]['string'] = strlen($dict['value']['announce-list']['value'][0]['value'][0]['value']).":".$dict['value']['announce-list']['value'][0]['value'][0]['value'];
//$dict['value']['announce-list']['value'][0]['value'][0]['strlen'] = strlen($dict['value']['announce-list']['value'][0]['value'][0]['string']);
//}


//Added support for using openbittorrent tracker
$announce_list = array(	
	       array(
    	         'value' => $dict['value']['announce']['value'],
  		   'type' => 'string')
		 );

$announce_list2 = array(	
	       array(
    		  'value' => $announce_urls[1],
    		  'type' => 'string')
		 );


#two trackers
#$dict['value']['announce-list']['value'] = array( array( 'value' => $announce_list, 'type' => 'list' ),array( 'value' => $announce_list2, 'type' => 'list' ));

#one tracker
$dict['value']['announce-list']['value'] = array( array( 'value' => $announce_list, 'type' => 'list' ));

$dict['value']['announce-list']['type'] = 'list';


header('Content-Disposition: attachment; filename="'.$row['filename'].'"');

header("Content-Type: application/x-bittorrent");



print(benc($dict));


?>