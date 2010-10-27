<?php

require_once "include/bittorrent.php";
require_once "include/user_functions.php";

function bark($msg) {
	genbark($msg, "Update failed!");
}

dbconn();

loggedinorreturn();

if (!mkglobal("email:chpassword:passagain:chmailpass"))
	bark("missing form data");

// $set = array();

$updateset = array();
$changedemail = 0;

if ($chpassword != "") {
	if (strlen($chpassword) > 40)
		bark("Sorry, password is too long (max is 40 chars)");
	if ($chpassword != $passagain)
		bark("The passwords didn't match. Try again.");

	$sec = mksecret();

  $passhash = md5($sec . $chpassword . $sec);

	$updateset[] = "secret = " . sqlesc($sec);
	$updateset[] = "passhash = " . sqlesc($passhash);
	logincookie($CURUSER["id"], $passhash);
}

if ($email != $CURUSER["email"]) {
	if (!validemail($email))
		bark("That doesn't look like a valid email address.");
  $r = mysql_query("SELECT id FROM users WHERE email=" . sqlesc($email)) or sqlerr();
	if ( mysql_num_rows($r) > 0 || ($CURUSER["passhash"] != md5($CURUSER["secret"] . $chmailpass . $CURUSER["secret"])) )
		bark("Could not change email, address already taken or password mismatch.");
	$changedemail = 1;
}


$acceptpms = $_POST["acceptpms"];
$deletepms = isset($_POST["deletepms"]) ? "yes" : "no";
$savepms = (isset($_POST['savepms']) && $_POST["savepms"] != "" ? "yes" : "no");
$pmnotif = isset($_POST["pmnotif"]) ? $_POST["pmnotif"] : '';
$emailnotif = isset($_POST["emailnotif"]) ? $_POST["emailnotif"] : '';
$notifs = ($pmnotif == 'yes' ? "[pm]" : "");
$notifs .= ($emailnotif == 'yes' ? "[email]" : "");
$r = mysql_query("SELECT id FROM categories") or sqlerr();
$rows = mysql_num_rows($r);
for ($i = 0; $i < $rows; ++$i)
{
	$a = mysql_fetch_assoc($r);
	if (isset($_POST["cat{$a['id']}"]) && $_POST["cat{$a['id']}"] == 'yes')
	  $notifs .= "[cat{$a['id']}]";
}
$avatar = $_POST["avatar"];
$avatars = ($_POST["avatars"] != "" ? "yes" : "no");
$avatar = trim( urldecode( $avatar ) );
if ( preg_match( "/^http:\/\/$/i", $avatar ) or
preg_match( "/[?&;]/",
$avatar ) or preg_match( "#javascript:#is", $avatar )
or !preg_match(
"#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-
!]+)$#iU", $avatar ) )
{
$avatar='';
}

// $ircnick = $_POST["ircnick"];
// $ircpass = $_POST["ircpass"];
$info = $_POST["info"];
$stylesheet = $_POST["stylesheet"];
$country = $_POST["country"];

if(isset($_POST["user_timezone"]) && preg_match('#^\-?\d{1,2}(?:\.\d{1,2})?$#', $_POST['user_timezone']))
$updateset[] = "time_offset = " . sqlesc($_POST['user_timezone']);

$updateset[] = "auto_correct_dst = " .(isset($_POST['checkdst']) ? 1 : 0);
$updateset[] = "dst_in_use = " .(isset($_POST['manualdst']) ? 1 : 0);

/*
if ($privacy != "normal" && $privacy != "low" && $privacy != "strong")
	bark("whoops");

$updateset[] = "privacy = '$privacy'";
*/

$updateset[] = "torrentsperpage = " . min(100, 0 + $_POST["torrentsperpage"]);
$updateset[] = "topicsperpage = " . min(100, 0 + $_POST["topicsperpage"]);
$updateset[] = "postsperpage = " . min(100, 0 + $_POST["postsperpage"]);

if (is_valid_id($stylesheet))
  $updateset[] = "stylesheet = '$stylesheet'";
  
if (is_valid_id($country))
  $updateset[] = "country = $country";


$updateset[] = "info = " . sqlesc($info);
$updateset[] = "acceptpms = " . sqlesc($acceptpms);
$updateset[] = "deletepms = '$deletepms'";
$updateset[] = "savepms = '$savepms'";
$updateset[] = "notifs = '$notifs'";
$updateset[] = "avatar = " . sqlesc($avatar);
$updateset[] = "avatars = '$avatars'";

/* ****** */

$urladd = "";

if ($changedemail) {
	$sec = mksecret();
	$hash = md5($sec . $email . $sec);
	$obemail = urlencode($email);
	$updateset[] = "editsecret = " . sqlesc($sec);
	$thishost = $_SERVER["HTTP_HOST"];
	$thisdomain = preg_replace('/^www\./is', "", $thishost);
	$body = <<<EOD
You have requested that your user profile (username {$CURUSER["username"]})
on $thisdomain should be updated with this email address ($email) as
user contact.

If you did not do this, please ignore this email. The person who entered your
email address had the IP address {$_SERVER["REMOTE_ADDR"]}. Please do not reply.

To complete the update of your user profile, please follow this link:

$BASEURL/confirmemail.php/{$CURUSER["id"]}/$hash/$obemail

Your new email address will appear in your profile after you do this. Otherwise
your profile will remain unchanged.
EOD;

	mail($email, "$thisdomain profile change confirmation", $body, "From: $SITEEMAIL", "-f$SITEEMAIL");

	$urladd .= "&mailsent=1";
}

@mysql_query("UPDATE users SET " . implode(", ", $updateset) . " WHERE id = " . $CURUSER["id"]) or sqlerr(__FILE__,__LINE__);

header("Location: $BASEURL/my.php?edited=1" . $urladd);

?>