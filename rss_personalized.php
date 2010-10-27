<?php

require_once("include/bittorrent.php");
require_once "include/user_functions.php";
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
$rss_url = 'http://www.biotorrents.net/rss.php';
$passkey=$CURUSER['passkey'];
$passhash=$CURUSER['passhash'];
$uid=$CURUSER['id'];

$rss_url_with_cookie = $rss_url.':COOKIE:uid='.$uid.';pass='.$passhash;
//header( "Location:$rss_url_with_cookie" );
stdhead("Personalized RSS Feed");
echo("<b>The following url can be used with some torrent clients (e.g. utorrent) to download using your personal account.</b><br/><br/>");
echo($rss_url_with_cookie);
stdfoot();
?>