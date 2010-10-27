<?php

require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn(false);
loggedinorreturn();

$userid = isset($_GET['id']) ? (int)$_GET['id'] : $CURUSER['id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

//if (!$userid)
//	$userid = $CURUSER['id'];

if (!is_valid_id($userid))
	stderr("Error", "Invalid ID.");

if ($userid != $CURUSER["id"])
	stderr("Error", "Access denied.");


// action: add -------------------------------------------------------------

if ($action == 'add')
{
	$targetid = 0+$_GET['targetid'];
	$type = $_GET['type'];

  if (!is_valid_id($targetid))
		stderr("Error", "Invalid ID.");

  if ($type == 'friend')
  {
  	$table_is = $frag = 'friends';
    $field_is = 'friendid';
  }
	elseif ($type == 'block')
  {
		$table_is = $frag = 'blocks';
    $field_is = 'blockid';
  }
	else
		stderr("Error", "Unknown type.");

  $r = mysql_query("SELECT id FROM $table_is WHERE userid=$userid AND $field_is=$targetid") or sqlerr(__FILE__, __LINE__);
  if (mysql_num_rows($r) == 1)
		stderr("Error", "User ID is already in your ".htmlentities($table_is)." list.");

	mysql_query("INSERT INTO $table_is VALUES (0,$userid, $targetid)") or sqlerr(__FILE__, __LINE__);
  header("Location: $BASEURL/friends.php?id=$userid#$frag");
  die;
}

// action: delete ----------------------------------------------------------

if ($action == 'delete')
{
	$targetid = (int)$_GET['targetid'];
	$sure = isset($_GET['sure']) ? htmlentities($_GET['sure']) : false;
	$type = isset($_GET['type']) ? ($_GET['type'] == 'friend' ? 'friend' : 'block') : stderr('Error', 'LoL');

  if (!is_valid_id($targetid))
		stderr("Error", "Invalid ID.");

  if (!$sure)
    stderr("Delete $type","Do you really want to delete a $type? Click\n" .
    	"<a href='?id=$userid&amp;action=delete&amp;type=$type&amp;targetid=$targetid&amp;sure=1'>here</a> if you are sure.");

  if ($type == 'friend')
  {
    mysql_query("DELETE FROM friends WHERE userid=$userid AND friendid=$targetid") or sqlerr(__FILE__, __LINE__);
    if (mysql_affected_rows() == 0)
      stderr("Error", "No friend found with ID");
    $frag = "friends";
  }
  elseif ($type == 'block')
  {
    mysql_query("DELETE FROM blocks WHERE userid=$userid AND blockid=$targetid") or sqlerr(__FILE__, __LINE__);
    if (mysql_affected_rows() == 0)
      stderr("Error", "No block found with ID");
    $frag = "blocks";
  }
  else
    stderr("Error", "Unknown type.");

  header("Location: $BASEURL/friends.php?id=$userid#$frag");
  die;
}

// main body  -----------------------------------------------------------------

$res = mysql_query("SELECT * FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);
$user = mysql_fetch_assoc($res) or stderr("Error", "No user with ID.");

$donor = ($user["donor"] == "yes") ? "<img src='{$pic_base_url}starbig.gif' alt='Donor' style='margin-left: 4pt' />" : '';
$warned = ($user["warned"] == "yes") ? "<img src='{$pic_base_url}warnedbig.gif' alt='Warned' style='margin-left: 4pt' />" : '';


stdhead("Personal lists for " . htmlentities($user['username'], ENT_QUOTES));


print("<p></p><table class='main' border='0' cellspacing='0' cellpadding='0'>".
"<tr><td class='embedded'><h1 style='margin:0px'> Personal lists for ".htmlentities($user['username'], ENT_QUOTES)."</h1>$donor$warned</td></tr></table><p></p>\n");

print("<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>");

print("<br />");
print("<h2 align='left'><a name=\"friends\">Friends list</a></h2>\n");

print("<table width='750' border='1' cellspacing='0' cellpadding='5'><tr><td>");

$i = 0;

$res = mysql_query("SELECT f.friendid as id, u.username AS name, u.class, u.avatar, u.title, u.donor, u.warned, u.enabled, u.last_access FROM friends AS f LEFT JOIN users as u ON f.friendid = u.id WHERE userid=$userid ORDER BY name") or sqlerr(__FILE__, __LINE__);
if(mysql_num_rows($res) == 0)
	echo "<em>Your friends list is empty.</em>";
else
	while ($friend = mysql_fetch_assoc($res))
	{
    $title = $friend["title"];
		if (!$title)
	    $title = get_user_class_name($friend["class"]);
    $body1 = "<a href='userdetails.php?id=" . $friend['id'] . "'><b>" . $friend['name'] . "</b></a>" .
    	get_user_icons($friend) . " ($title)<br /><br />last seen on " . get_date( $friend['last_access'],'') .
    	"<br />(" . get_date( $friend['last_access'],'',0,1) . ")";
		$body2 = "<br /><a href='friends.php?id=$userid&amp;action=delete&amp;type=friend&amp;targetid=" . $friend['id'] . "'>Remove</a>" .
			"<br /><br /><a href='sendmessage.php?receiver=" . $friend['id'] . "'>Send PM</a>";
    $avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($friend["avatar"]) : "");
		if (!$avatar)
			$avatar = "{$pic_base_url}forumicons/default_avatar.gif";
    if ($i % 2 == 0)
    	print("<table width='100%' style='padding: 0px'><tr><td class='bottom' style='padding: 5px' width='50%' align='center'>");
    else
    	print("<td class='bottom' style='padding: 5px' width='50%' align='center'>");
    print("<table class='main' width='100%'>");
    print("<tr valign='top'><td width='75' align='center' style='padding: 0px'>" .
			($avatar ? "<div style='width:75px;height:75px;overflow: hidden'><img width='75px' src=\"$avatar\" alt='' /></div>" : ""). "</td><td>\n");
    print("<table class='main'>");
    print("<tr><td class='embedded' style='padding: 5px' width='80%'>$body1</td>\n");
    print("<td class='embedded' style='padding: 5px' width='20%'>$body2</td></tr>\n");
    print("</td></tr></table>");
		print("</td></tr></table>");
		print("</td></tr></table>\n");
    if ($i % 2 == 1)
			print("</td></tr></table>\n");
		else
			print("</td>\n");
		$i++;
	}
if ($i % 2 == 1)
	print("<td class='bottom' width='50%'>&nbsp;</td></tr></table>\n");
//print($friends);
print("</td></tr></table>\n");

$res = mysql_query("SELECT b.blockid as id, u.username AS name, u.donor, u.warned, u.enabled, u.last_access FROM blocks AS b LEFT JOIN users as u ON b.blockid = u.id WHERE userid=$userid ORDER BY name") or sqlerr(__FILE__, __LINE__);
if(mysql_num_rows($res) == 0)
	$blocks = "<em>Your blocked users list is empty.</em>";
else
{
	$i = 0;
	$blocks = "<table width='100%' cellspacing='0' cellpadding='0'>";
	while ($block = mysql_fetch_assoc($res))
	{
		if ($i % 6 == 0)
			$blocks .= "<tr>";
    	$blocks .= "<td style='border: none; padding: 4px; spacing: 0px;'>[<font class='small'><a href='friends.php?id=$userid&amp;action=delete&amp;type=block&amp;targetid=" .
				$block['id'] . "'>D</a></font>] <a href='userdetails.php?id=" . $block['id'] . "'><b>" . $block['name'] . "</b></a>" .
				get_user_icons($block) . "</td>";
		if ($i % 6 == 5)
			$blocks .= "</tr>";
		$i++;
	}
	print("</table>\n");
}
print("<br /><br />");
print("<table class='main' width='750' border='0' cellspacing='0' cellpadding='10'><tr><td class='embedded'>");
print("<h2 align='left'><a name=\"blocks\">Blocked users list</a></h2></td></tr>");
print("<tr><td style='padding: 10px;background-color: #ECE9D8'>");
print("$blocks\n");
print("</td></tr></table>\n");
print("</td></tr></table>\n");
print("<p><a href='users.php'><b>Find User/Browse User List</b></a></p>");
stdfoot();
?>