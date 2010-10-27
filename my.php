<?php

require_once "include/bittorrent.php";
require_once "include/html_functions.php";
require_once "include/user_functions.php";
require_once ROOT_PATH."/cache/timezones.php";

dbconn(false);

loggedinorreturn();
/*
$res = mysql_query("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " AND location IN ('in', 'both')") or print(mysql_error());
$arr = mysql_fetch_row($res);
$messages = $arr[0];
$res = mysql_query("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " AND location IN ('in', 'both') AND unread='yes'") or print(mysql_error());
$arr = mysql_fetch_row($res);
$unread = $arr[0];
$res = mysql_query("SELECT COUNT(*) FROM messages WHERE sender=" . $CURUSER["id"] . " AND location IN ('out', 'both')") or print(mysql_error());
$arr = mysql_fetch_row($res);
$outmessages = $arr[0];
*/


stdhead(htmlentities($CURUSER["username"], ENT_QUOTES) . "'s private page", false);

if($CURUSER['id'] ==4)
{
  stdmsg("Sorry...", "You are not authorized to view this page.  Please <a class='morgan' href='login.php'>Login</a> or <a class='morgan' href='signup.php'>Sign up</a> for your own account.");
  stdfoot();
  exit;
}

if (isset($_GET["edited"])) {
	print("<h1>Profile updated!</h1>\n");
	if (isset($_GET["mailsent"]))
		print("<h2>Confirmation email has been sent!</h2>\n");
}
elseif (isset($_GET["emailch"]))
	print("<h1>Email address changed!</h1>\n");
//else
	//print("<h1>Welcome, <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>!</h1>\n");
$user_header = "<span style='font-size: 20px;'><a href='userdetails.php?id={$CURUSER['id']}'>{$CURUSER['username']}</a></span>";
if(!empty($CURUSER['avatar']) && $CURUSER['av_w'] > 5 && $CURUSER['av_h'] > 5)
$avatar = "<img src='{$CURUSER['avatar']}' width='{$CURUSER['av_w']}' height='{$CURUSER['av_h']}' alt='' />";
else
$avatar = "<img src='{$pic_base_url}forumicons/default_avatar.gif' alt='' />";
?>

<script type="text/javascript">

function daylight_show()
{
	if ( document.getElementById( 'tz-checkdst' ).checked )
	{
		document.getElementById( 'tz-checkmanual' ).style.display = 'none';
	}
	else
	{
		document.getElementById( 'tz-checkmanual' ).style.display = 'block';
	}
}

</script>


<table border="1" cellspacing="0" cellpadding="10" align="center">
<!--<tr>
<td align="center" width="33%"><a href='logout.php'><b>Logout</b></a></td>
<td align="center" width="33%"><a href='mytorrents.php'><b>My torrents</b></a></td>
<td align="center" width="33%"><a href='friends.php'><b>My users lists</b></a></td>
</tr>-->
<tr>
  <td valign="top">
  <?php echo $user_header?><br />
  <?php echo $avatar?><br />
  <a href='mytorrents.php'>View/Edit your Torrents</a><br />
  <a href='friends.php'>View/Edit your Friends</a><br />
  <a href='users.php'>Search Members</a>
  </td>
<td>
<form method="post" action="takeprofedit.php">
<table border="1" cellspacing='0' cellpadding="5" width="100%">
<?php

/***********************

$res = mysql_query("SELECT COUNT(*) FROM ratings WHERE user=" . $CURUSER["id"]);
$row = mysql_fetch_array($res,MYSQL_NUM);
tr("Ratings submitted", $row[0]);

$res = mysql_query("SELECT COUNT(*) FROM comments WHERE user=" . $CURUSER["id"]);
$row = mysql_fetch_array($res,MYSQL_NUM);
tr("Written comments", $row[0]);

****************/
$stylesheets ='';
$ss_r = mysql_query("SELECT * from stylesheets") or die;
$ss_sa = array();
while ($ss_a = mysql_fetch_assoc($ss_r))
{
  $ss_id = $ss_a["id"];
  $ss_name = $ss_a["name"];
  $ss_sa[$ss_name] = $ss_id;
}
ksort($ss_sa);
reset($ss_sa);
while (list($ss_name, $ss_id) = each($ss_sa))
{
  if ($ss_id == $CURUSER["stylesheet"]) $ss = " selected='selected'"; else $ss = "";
  $stylesheets .= "<option value='$ss_id'$ss>$ss_name</option>\n";
}

$countries = "<option value='0'>---- None selected ----</option>\n";
$ct_r = mysql_query("SELECT id,name FROM countries ORDER BY name") or die;
while ($ct_a = mysql_fetch_assoc($ct_r))
  $countries .= "<option value='$ct_a[id]'" . ($CURUSER["country"] == $ct_a['id'] ? " selected='selected'" : "") . ">$ct_a[name]</option>\n";

 		//-----------------------------------------
 		// Work out the timezone selection
 		//-----------------------------------------
		$offset = ($CURUSER['time_offset'] != "") ? (string)$CURUSER['time_offset'] : (string)$CONFIG_INFO['time_offset'];
 		
 		$time_select = "<select name='user_timezone'>";
 		
 		//-----------------------------------------
 		// Loop through the langauge time offsets and names to build our
 		// HTML jump box.
 		//-----------------------------------------
 		
 		foreach( $lang as $off => $words )
 		{
 			if ( preg_match("/^time_(-?[\d\.]+)$/", $off, $match))
 			{
				$time_select .= $match[1] == $offset ? "<option value='{$match[1]}' selected='selected'>$words</option>\n" : "<option value='{$match[1]}'>$words</option>\n";
 			}
 		}
 		
 		$time_select .= "</select>";
 
 		//-----------------------------------------
 		// DST IN USE?
 		//-----------------------------------------
 		
 		if ($CURUSER['dst_in_use'])
 		{
 			$dst_check = 'checked="checked"';
 		}
 		else
 		{
 			$dst_check = '';
 		}
 		
 		//-----------------------------------------
 		// DST CORRECTION IN USE?
 		//-----------------------------------------
 		
 		if ($CURUSER['auto_correct_dst'])
 		{
 			$dst_correction = 'checked="checked"';
 		}
 		else
 		{
 			$dst_correction = '';
 		}
 		
 		
tr("Accept PMs",
"<input type='radio' name='acceptpms'" . ($CURUSER["acceptpms"] == "yes" ? " checked='checked'" : "") . " value='yes' />All (except blocks)
<input type='radio' name='acceptpms'" .  ($CURUSER["acceptpms"] == "friends" ? " checked='checked'" : "") . " value='friends' />Friends only
<input type='radio' name='acceptpms'" .  ($CURUSER["acceptpms"] == "no" ? " checked='checked'" : "") . " value='no' />Staff only"
,1);



tr("Delete PMs", "<input type='checkbox' name='deletepms'" . ($CURUSER["deletepms"] == "yes" ? " checked='checked'" : "") . " /> (Default value for \"Delete PM on reply\")",1);
tr("Save PMs", "<input type='checkbox' name='savepms'" . ($CURUSER["savepms"] == "yes" ? " checked='checked'" : "") . " /> (Default value for \"Save PM to Sentbox\")",1);
$categories = '';
$r = mysql_query("SELECT id,name FROM categories ORDER BY name") or sqlerr();
//$categories = "Default browsing categories:<br>\n";
if (mysql_num_rows($r) > 0)
{
	$categories .= "<table><tr>\n";
	$i = 0;
	while ($a = mysql_fetch_assoc($r))
	{
	  $categories .=  ($i && $i % 2 == 0) ? "</tr><tr>" : "";
	  $categories .= "<td class='bottom' style='padding-right: 5px'><input name='cat$a[id]' type=\"checkbox\" " . (strpos($CURUSER['notifs'], "[cat$a[id]]") !== false ? " checked='checked'" : "") . " value='yes' />&nbsp;" . htmlspecialchars($a["name"]) . "</td>\n";
	  ++$i;
	}
	$categories .= "</tr></table>\n";
}

tr("Email notification", "<input type='checkbox' name='pmnotif'" . (strpos($CURUSER['notifs'], "[pm]") !== false ? " checked='checked'" : "") . " value='yes' /> Notify me when I have received a PM<br />\n" .
	 "<input type='checkbox' name='emailnotif'" . (strpos($CURUSER['notifs'], "[email]") !== false ? " checked='checked'" : "") . " value='yes' /> Notify me when a torrent is uploaded in one of <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; my default browsing categories.\n"
   , 1);
tr("Browse default<br />categories",$categories,1);
tr("Stylesheet", "<select name='stylesheet'>\n$stylesheets\n</select>",1);
tr("Country", "<select name='country'>\n$countries\n</select>",1);

// Timezone stuff //
tr("Timezone", $time_select ,1);
tr("Daylight Saving", "<input type='checkbox' name='checkdst' id='tz-checkdst' onclick='daylight_show()' value='1' $dst_correction />&nbsp;Auto correct DST?<br />
<div id='tz-checkmanual' style='display: none;'><input type='checkbox' name='manualdst' value='1' $dst_check />&nbsp;Is daylight saving time in effect?</div>",1);
// Timezone stuff end //

tr("Avatar URL", "<input name='avatar' size='50' value=\"" . htmlspecialchars($CURUSER["avatar"]) .
  "\" /><br />\nWidth should be 150 pixels (will be resized if necessary)\n<br />If you need a host for the picture, try the <a href='bitbucket-upload.php'>bitbucket</a>.",1);
tr("Torrents per page", "<input type='text' size='10' name='torrentsperpage' value='$CURUSER[torrentsperpage]' /> (0=use default setting)",1);
tr("Topics per page", "<input type='text' size='10' name='topicsperpage' value='$CURUSER[topicsperpage]' /> (0=use default setting)",1);
tr("Posts per page", "<input type='text' size='10' name='postsperpage' value='$CURUSER[postsperpage]' /> (0=use default setting)",1);
tr("View avatars", "<input type='checkbox' name='avatars'" . ($CURUSER["avatars"] == "yes" ? " checked='checked'" : "") . " /> (Low bandwidth users might want to turn this off)",1);
tr("Info", "<textarea name='info' cols='50' rows='4'>" . htmlentities($CURUSER["info"], ENT_QUOTES) . "</textarea><br />Displayed on your public page. May contain <a href='tags.php' target='_new'>BB codes</a>.", 1);
tr("Email address", "<input type=\"text\" name=\"email\" size='50' value=\"" . htmlspecialchars($CURUSER["email"]) . "\" /><br />Please enter your password if changing your email address!<br /><input type=\"password\" name=\"chmailpass\" size=\"50\" />", 1);
print("<tr><td colspan=\"2\" align='left'><b>Note:</b> In order to change your email address, you will receive another<br />confirmation email to your new address.</td></tr>\n");
tr("Change password", "<input type=\"password\" name=\"chpassword\" size=\"50\" />", 1);
tr("Type password again", "<input type=\"password\" name=\"passagain\" size=\"50\" />", 1);

function priv($name, $descr) {
	global $CURUSER;
	if ($CURUSER["privacy"] == $name)
		return "<input type=\"radio\" name=\"privacy\" value=\"$name\" checked=\"checked\" /> $descr";
	return "<input type=\"radio\" name=\"privacy\" value=\"$name\" /> $descr";
}

/* tr("Privacy level",  priv("normal", "Normal") . " " . priv("low", "Low (email address will be shown)") . " " . priv("strong", "Strong (no info will be made available)"), 1); */

?>
<tr><td colspan="2" align="center"><input type="submit" value="Submit changes!" class="btn" /> <input type="reset" value="Revert changes!" class="btn" /></td></tr>
</table>
</form>
</td>
</tr>
</table>
<?php
/*
if ($messages){
  print("<p>You have $messages message" . ($messages != 1 ? "s" : "") . " ($unread new) in your <a href='inbox.php'><b>inbox</b></a>,<br />\n");
	if ($outmessages)
		print("and $outmessages message" . ($outmessages != 1 ? "s" : "") . " in your <a href='inbox.php?out=1'><b>sentbox</b></a>.\n</p>");
	else
		print("and your <a href='inbox.php?out=1'>sentbox</a> is empty.</p>");
}
else
{
  print("<p>Your <a href='inbox.php'>inbox</a> is empty, <br />\n");
	if ($outmessages)
		print("and you have $outmessages message" . ($outmessages != 1 ? "s" : "") . " in your <a href='inbox.php?out=1'><b>sentbox</b></a>.\n</p>");
	else
		print("and so is your <a href='inbox.php?out=1'>sentbox</a>.</p>");
}
*/
//print("<p><a href='users.php'><b>Find User/Browse User List</b></a></p>");
stdfoot();

?>