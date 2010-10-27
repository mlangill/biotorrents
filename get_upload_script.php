<?php

require_once("include/bittorrent.php");

dbconn();

loggedinorreturn();



if($CURUSER['id'] ==4)
{
  stdhead();
  stdmsg("Sorry...", "You are not authorized to view this page.  Please <a href='login.php'>Login</a> or <a href='signup.php'>Sign up</a> for your own account.");
  stdfoot();
  exit;
}


if (!isset($CURUSER['passkey']) || strlen($CURUSER['passkey']) != 32) {

  $CURUSER['passkey'] = md5($CURUSER['username'].time().$CURUSER['passhash']);

  mysql_query("UPDATE users SET passkey='{$CURUSER['passkey']}' WHERE id={$CURUSER['id']}");

}
$file_name = 'upload_to_biotorrents_as_'.$CURUSER['username'].'.pl.';
$passkey=$CURUSER['passkey'];
$passhash=$CURUSER['passhash'];
$uid=$CURUSER['id'];

$script_header='#!/usr/bin/perl'."
my \$uid = '$uid';
my \$pass = '$passhash';
my \$passkey = '$passkey';\n\n";

$script_file='scripts/upload_to_biotorrents.pl';
$fh = fopen($script_file, 'r') or die("Can't read file: $script_file");

header("Content-Disposition: attachment; filename=$file_name");
header("Content-Type: text/plain");
echo $script_header;


while (!feof($fh)) {
	echo fread($fh, 65536);
        flush();
}
fclose($fh);

?>