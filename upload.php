<?php

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/html_functions.php";

dbconn(false);

loggedinorreturn();

stdhead("Upload");

#if ($CURUSER['class'] < UC_UPLOADER)
if($CURUSER['id'] ==4)
{
  stdmsg("Sorry...", "You are not authorized to upload new torrents.  Please <a class='morgan' href='login.php'>Login</a> or <a class='morgan' href='signup.php'>Sign up</a> for your own account. 
<br><br>(See <a class='morgan' href=\"faq.php#up1\">Uploading</a> in the FAQ for why we can't allow anonymous uploading of new torrents.)");
  stdfoot();
  exit;
}
if (!isset($CURUSER['passkey']) || strlen($CURUSER['passkey']) != 32) {

$CURUSER['passkey'] = md5($CURUSER['username'].time().$CURUSER['passhash']);

mysql_query("UPDATE users SET passkey='{$CURUSER['passkey']}' WHERE id={$CURUSER['id']}");

}
?>
<div align='center'>
<form enctype="multipart/form-data" action="takeupload.php" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_torrent_size?>" />
<p>The tracker's announce url is <br/><b><?php echo  "$announce_urls[0]?passkey={$CURUSER['passkey']}"?></b></p>
<!--<p>The tracker's announce url is <b><?php echo  $announce_urls[0]?></b></p>-->
<p>Please see the <a href="faq.php#up2">FAQ:Upload</a> section for more information.</p>
<table border="1" cellspacing="0" cellpadding="10">
<?php

tr("Torrent file", "<input type='file' name='file' size='80' />\n", 1);
tr("Torrent name", "<input type=\"text\" name=\"name\" size=\"80\" /><br />(Taken from filename if not specified. <b>Please use descriptive names.</b>)\n", 1);
#tr("NFO file", "<input type='file' name='nfo' size='80' /><br />(<b>Optional.</b> Can only be viewed by power users.)\n", 1);
tr("Description", "<textarea name=\"descr\" rows=\"10\" cols=\"80\"></textarea>" .
  "<br />(HTML/BB code is <b>not</b> allowed.)", 1);

#category
$s = "<select name=\"type\">\n<option value=\"0\">(choose one)</option>\n";
$cats = genrelist();
foreach ($cats as $row)
	$s .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</option>\n";

$s .= "</select>\n";
tr("Category", $s, 1);

#Morgan: license
$lic = "<select name=\"lic\">\n<option value=\"0\">(choose one)</option>\n";
$lic_cats = licenselist();
foreach ($lic_cats as $row)
	$lic .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) .' (' . htmlspecialchars($row["description"]) .')'. "</option>\n";

$lic .= "</select>\n";
tr("License", $lic, 1);

#Morgan: version
$version = "<select name=\"version\">\n<option value=\"0\">(Select alternative version of this torrent)</option>\n";
$version_list = torrentlist();
foreach ($version_list as $row)
	$version .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</option>\n";

$version .= "</select>\n";
tr("Version Group<br />(optional)", $version, 1);
?>
<tr><td align="center" colspan="2"><input type="submit" class='btn' value="Do it!" /></td></tr>
</table>
</form>
</div>
<?php

stdfoot();

?>