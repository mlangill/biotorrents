<?php

require_once("include/benc.php");
require_once("include/bittorrent.php");
require_once "include/user_functions.php";

ini_set("upload_max_filesize",$max_torrent_size);

function bark($msg) {
	genbark($msg, "Upload failed!");
}

dbconn(); 

loggedinorreturn();

if ($CURUSER['class'] < UC_USER)
  die;

foreach(explode(":","descr:type:name") as $v) {
	if (!isset($_POST[$v]))
		bark("missing form data");
}

if (!isset($_FILES["file"]))
	bark("missing form data: file");

$f = $_FILES["file"];
$fname = unesc($f["name"]);
if (empty($fname))
	bark("Empty filename!");
	
$nfo = sqlesc('');
/////////////////////// NFO FILE ////////////////////////	
if(isset($_FILES['nfo']) && !empty($_FILES['nfo']['name'])) {
$nfofile = $_FILES['nfo'];
if ($nfofile['name'] == '')
  bark("No NFO!");

if ($nfofile['size'] == 0)
  bark("0-byte NFO");

if ($nfofile['size'] > 65535)
  bark("NFO is too big! Max 65,535 bytes.");

$nfofilename = $nfofile['tmp_name'];

if (@!is_uploaded_file($nfofilename))
  bark("NFO upload failed");

$nfo = sqlesc(str_replace("\x0d\x0d\x0a", "\x0d\x0a", @file_get_contents($nfofilename)));
}
/////////////////////// NFO FILE END /////////////////////

$descr = unesc($_POST["descr"]);
if (!$descr)
  bark("You must enter a description!");

$catid = (0 + $_POST["type"]);
if (!is_valid_id($catid))
	bark("You must select a category to put the torrent in!");
	
$lic_id =(0 + $_POST["lic"]);
if (!is_valid_id($lic_id))
	bark("You must select a license category for the torrent!");

$version_torrent_id =(0 + $_POST["version"]);

if (!validfilename($fname))
	bark("Invalid filename!");
if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
	bark("Invalid filename (not a .torrent).");
$shortfname = $torrent = $matches[1];
if (!empty($_POST["name"]))
	$torrent = unesc($_POST["name"]);

$tmpname = $f["tmp_name"];
if (!is_uploaded_file($tmpname))
	bark("eek");
if (!filesize($tmpname))
	bark("Empty file!");

$dict = bdec_file($tmpname, $max_torrent_size);
if (!isset($dict))
	bark("What the hell did you upload? This is not a bencoded file!");


function dict_check($d, $s) {
	if ($d["type"] != "dictionary")
		bark("not a dictionary");
	$a = explode(":", $s);
	$dd = $d["value"];
	$ret = array();
	$t='';
	foreach ($a as $k) {
		unset($t);
		if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
			$k = $m[1];
			$t = $m[2];
		}
		if (!isset($dd[$k]))
			bark("dictionary is missing key(s)");
		if (isset($t)) {
			if ($dd[$k]["type"] != $t)
				bark("invalid entry in dictionary");
			$ret[] = $dd[$k]["value"];
		}
		else
			$ret[] = $dd[$k];
	}
	return $ret;
}

function dict_get($d, $k, $t) {
	if ($d["type"] != "dictionary")
		bark("not a dictionary");
	$dd = $d["value"];
	if (!isset($dd[$k]))
		return;
	$v = $dd[$k];
	if ($v["type"] != $t)
		bark("invalid dictionary entry type");
	return $v["value"];
}

list($ann, $info) = dict_check($dict, "announce(string):info");

$tmaker = (isset($dict['value']['created by']) && !empty($dict['value']['created by']['value'])) ? sqlesc($dict['value']['created by']['value']) : sqlesc('Unknown');

//Morgan: Hack to strip hashkey from announce string
$my_dict=$dict;

//Overwrite user supplied announce url with simple biotorrent one
$my_dict['value']['announce']['value'] = $announce_urls[0];
$my_dict['value']['announce']['string'] = strlen($my_dict['value']['announce']['value']).":".$my_dict['value']['announce']['value'];
$my_dict['value']['announce']['strlen'] = strlen($my_dict['value']['announce']['string']);

//Added support for announce-list
if(isset($my_dict['value']['announce-list']['value'][0]['value'][0]['value'])){
$my_dict['value']['announce-list']['value'][0]['value'][0]['value'] = $my_dict['value']['announce']['value'];
$my_dict['value']['announce-list']['value'][0]['value'][0]['string'] = strlen($my_dict['value']['announce-list']['value'][0]['value'][0]['value']).":".$my_dict['value']['announce-list']['value'][0]['value'][0]['value'];
$my_dict['value']['announce-list']['value'][0]['value'][0]['strlen'] = strlen($my_dict['value']['announce-list']['value'][0]['value'][0]['string']);
}

unset($dict);

list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");

#if (!in_array($ann, $announce_urls, 1))
 #   bark("invalid announce url! must be " . $announce_urls[0]);
if($ann != $announce_urls[0]."?passkey={$CURUSER['passkey']}")
	bark("invalid announce url! must be " . $announce_urls[0] ."?passkey={$CURUSER['passkey']}" . "not $ann");

if (strlen($pieces) % 20 != 0)
	bark("invalid pieces");

$filelist = array();
$totallen = dict_get($info, "length", "integer");
if (isset($totallen)) {
	$filelist[] = array($dname, $totallen);
	$type = "single";
}
else {
	$flist = dict_get($info, "files", "list");
	if (!isset($flist))
		bark("missing both length and files");
	if (!count($flist))
		bark("no files");
	$totallen = 0;
	foreach ($flist as $fn) {
		list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
		$totallen += $ll;
		$ffa = array();
		foreach ($ff as $ffe) {
			if ($ffe["type"] != "string")
				bark("filename error");
			$ffa[] = $ffe["value"];
		}
		if (!count($ffa))
			bark("filename error");
		$ffe = implode("/", $ffa);
		$filelist[] = array($ffe, $ll);
	}
	$type = "multi";
}


$infohash = pack("H*", sha1($info["string"]));

unset($info);
// Replace punctuation characters with spaces

$torrent = str_replace("_", " ", $torrent);


#Morgan: Add version insert if applicable
$version_id = get_version_id_for_torrent($version_torrent_id,0);

$ret = mysql_query("INSERT INTO torrents (search_text, filename, owner, visible, info_hash, name, size, numfiles, type,descr, ori_descr, category,license, save_as, added, last_action, nfo, client_created_by, version) VALUES (" .
		implode(",", array_map("sqlesc", array(searchfield("$shortfname $dname $torrent"), $fname, $CURUSER["id"], "no", $infohash, $torrent, $totallen, count($filelist), $type, $descr, $descr, $catid, $lic_id, $dname))) .
		", " . time() . ", " . time() . ", $nfo, $tmaker, $version_id)");
if (!$ret) {
	if (mysql_errno() == 1062)
		bark("torrent already uploaded!");
	bark("mysql puked: ".mysql_error());
}
$id = mysql_insert_id();

@mysql_query("DELETE FROM files WHERE torrent = $id");

function file_list($arr,$id)
{
    foreach($arr as $v)
        $new[] = "($id,".sqlesc($v[0]).",".$v[1].")";
    return join(",",$new);
}

mysql_query("INSERT INTO files (torrent, filename, size) VALUES ".file_list($filelist,$id));

//#Morgan: Instead of moving the uploaded torrent just create a new one with the simpler announce url
#move_uploaded_file($tmpname, "$torrent_dir/$id.torrent");


$morgan_fh = fopen("$torrent_dir/$id.torrent", 'w');
fwrite($morgan_fh,benc($my_dict));
fclose($morgan_fh);


write_log("Torrent $id ($torrent) was uploaded by " . $CURUSER["username"]);



/* RSS feeds */
require_once("rss_old.php");

/* Email notifs */
/*******************

$res = mysql_query("SELECT name FROM categories WHERE id=$catid") or sqlerr();
$arr = mysql_fetch_assoc($res);
$cat = $arr["name"];
$res = mysql_query("SELECT email FROM users WHERE enabled='yes' AND notifs LIKE '%[cat$catid]%'") or sqlerr();
$uploader = $CURUSER['username'];

$size = mksize($totallen);
$description = ($html ? strip_tags($descr) : $descr);

$body = <<<EOD
A new torrent has been uploaded.

Name: $torrent
Size: $size
Category: $cat
Uploaded by: $uploader

Description
-------------------------------------------------------------------------------
$description
-------------------------------------------------------------------------------

You can use the URL below to download the torrent (you may have to login).

$DEFAULTBASEURL/details.php?id=$id&hit=1

-- 
$SITENAME
EOD;
$to = "";
$nmax = 100; // Max recipients per message
$nthis = 0;
$ntotal = 0;
$total = mysql_num_rows($res);
while ($arr = mysql_fetch_row($res))
{
  if ($nthis == 0)
    $to = $arr[0];
  else
    $to .= "," . $arr[0];
  ++$nthis;
  ++$ntotal;
  if ($nthis == $nmax || $ntotal == $total)
  {
    if (!mail("Multiple recipients <$SITEEMAIL>", "New torrent - $torrent", $body,
    "From: $SITEEMAIL\r\nBcc: $to", "-f$SITEEMAIL"))
	  stderr("Error", "Your torrent has been been uploaded. DO NOT RELOAD THE PAGE!\n" .
	    "There was however a problem delivering the e-mail notifcations.\n" .
	    "Please let an administrator know about this error!\n");
    $nthis = 0;
  }
}
*******************/

header("Location: $BASEURL/details.php?id=$id&uploaded=1");

?>