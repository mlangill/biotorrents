<?php

require_once("include/bittorrent.php");
require_once "include/user_functions.php";

$action = $_GET["action"];

dbconn(false);

loggedinorreturn();

#do not allow guest users to add comments (too much spam)
if($CURUSER['id'] ==4)
{  
stdhead();
stdmsg("Sorry...", "You are not authorized to view this page.  Please <a  href='login.php'>Login</a> or <a href='signup.php'>Sign up</a> for your own account.");
  stdfoot();
  exit;
}

if ($action == "add")
{
  if ($_SERVER["REQUEST_METHOD"] == "POST")
  {
    $torrentid = 0 + $_POST["tid"];
	  if (!is_valid_id($torrentid))
			stderr("Error", "Invalid ID.");

		$res = @mysql_query("SELECT name FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__,__LINE__);
		$arr = mysql_fetch_array($res,MYSQL_NUM);
		if (!$arr)
		  stderr("Error", "No torrent with ID.");

	  $text = trim($_POST["text"]);
	  if (!$text)
			stderr("Error", "Comment body cannot be empty!");

	  @mysql_query("INSERT INTO comments (user, torrent, added, text, ori_text) VALUES (" .
	      $CURUSER["id"] . ",$torrentid, " . time() . ", " . sqlesc($text) .
	       "," . sqlesc($text) . ")");

	  $newid = mysql_insert_id();

	  @mysql_query("UPDATE torrents SET comments = comments + 1 WHERE id = $torrentid");

	  header("Refresh: 0; url=details.php?id=$torrentid&viewcomm=$newid#comm$newid");
	  die;
	}

  $torrentid = 0 + $_GET["tid"];
  if (!is_valid_id($torrentid))
		stderr("Error", "Invalid ID.");

	$res = mysql_query("SELECT name FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__,__LINE__);
	$arr = mysql_fetch_assoc($res);
	if (!$arr)
	  stderr("Error", "No torrent with ID.");

	stdhead("Add a comment to \"" . $arr["name"] . "\"");

	print("<h1>Add a comment to \"" . htmlspecialchars($arr["name"]) . "\"</h1>\n");
	print("<p><form method=\"post\" action=\"comment.php?action=add\">\n");
	print("<input type=\"hidden\" name=\"tid\" value=\"$torrentid\"/>\n");
	print("<textarea name=\"text\" rows=\"10\" cols=\"60\"></textarea></p>\n");
	print("<p><input type=\"submit\" class='btn' value=\"Do it!\" /></p></form>\n");

	$res = mysql_query("SELECT comments.id, text, comments.added, comments.editedby, comments.editedat, username, users.id as user, users.title, users.avatar, users.class, users.donor, users.warned FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = $torrentid ORDER BY comments.id DESC LIMIT 5");

	$allrows = array();
	while ($row = mysql_fetch_assoc($res))
	  $allrows[] = $row;

	if (count($allrows)) {
          require_once "include/torrenttable_functions.php";
          require_once "include/html_functions.php";
          require_once "include/bbcode_functions.php";
      print("<h2>Most recent comments, in reverse order</h2>\n");
      commenttable($allrows);
    }

  stdfoot();
	die;
}
elseif ($action == "edit")
{
  $commentid = 0 + $_GET["cid"];
  if (!is_valid_id($commentid))
		stderr("Error", "Invalid ID.");

  $res = mysql_query("SELECT c.*, t.name FROM comments AS c LEFT JOIN torrents AS t ON c.torrent = t.id WHERE c.id=$commentid") or sqlerr(__FILE__,__LINE__);
  $arr = mysql_fetch_assoc($res);
  if (!$arr)
  	stderr("Error", "Invalid ID.");

	if ($arr["user"] != $CURUSER["id"] && get_user_class() < UC_MODERATOR)
		stderr("Error", "Permission denied.");

	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
	  $text = $_POST["text"];
    $returnto = htmlspecialchars($_POST["returnto"]);

	  if ($text == "")
	  	stderr("Error", "Comment body cannot be empty!");

	  $text = sqlesc($text);

	  $editedat = time();

	  mysql_query("UPDATE comments SET text=$text, editedat=$editedat, editedby={$CURUSER['id']} WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);

		if ($returnto)
	  	header("Location: $returnto");
		else
		  header("Location: $BASEURL/");      // change later ----------------------
		die;
	}

 	stdhead("Edit comment to \"" . $arr["name"] . "\"");

	print("<h1>Edit comment to \"" . htmlspecialchars($arr["name"]) . "\"</h1><p>\n");
	print("<form method=\"post\" action=\"comment.php?action=edit&amp;cid=$commentid\">\n");
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . $_SERVER["HTTP_REFERER"] . "\" />\n");
	print("<input type=\"hidden\" name=\"cid\" value=\"$commentid\" />\n");
	print("<textarea name=\"text\" rows=\"10\" cols=\"60\">" . htmlspecialchars($arr["text"]) . "</textarea></p>\n");
	print("<p><input type=\"submit\" class='btn' value=\"Do it!\" /></p></form>\n");

	stdfoot();
	die;
}
elseif ($action == "delete")
{
	if (get_user_class() < UC_MODERATOR)
		stderr("Error", "Permission denied.");

  $commentid = 0 + $_GET["cid"];

  if (!is_valid_id($commentid))
		stderr("Error", "Invalid ID.");

  $sure = isset($_GET["sure"]) ? (int)$_GET["sure"] : false;

  if (!$sure)
  {
 		$referer = $_SERVER["HTTP_REFERER"];
		stderr("Delete comment", "You are about to delete a comment. Click\n" .
			"<a href='comment.php?action=delete&amp;cid=$commentid&amp;sure=1" .
			($referer ? "&amp;returnto=" . urlencode($referer) : "") .
			"'>here</a> if you are sure.");
  }


	$res = mysql_query("SELECT torrent FROM comments WHERE id=$commentid")  or sqlerr(__FILE__,__LINE__);
	$arr = mysql_fetch_assoc($res);
	if ($arr)
		$torrentid = $arr["torrent"];

	mysql_query("DELETE FROM comments WHERE id=$commentid") or sqlerr(__FILE__,__LINE__);
	if ($torrentid && mysql_affected_rows() > 0)
		mysql_query("UPDATE torrents SET comments = comments - 1 WHERE id = $torrentid");

	$returnto = $_GET["returnto"];

	if ($returnto)
	  header("Location: $returnto");
	else
	  header("Location: $BASEURL/");      // change later ----------------------
	die;
}
elseif ($action == "vieworiginal")
{
	if (get_user_class() < UC_MODERATOR)
		stderr("Error", "Permission denied.");

  $commentid = 0 + $_GET["cid"];

  if (!is_valid_id($commentid))
		stderr("Error", "Invalid ID.");

  $res = mysql_query("SELECT c.*, t.name FROM comments AS c LEFT JOIN torrents AS t ON c.torrent = t.id WHERE c.id=$commentid") or sqlerr(__FILE__,__LINE__);
  $arr = mysql_fetch_assoc($res);
  if (!$arr)
  	stderr("Error", "Invalid ID $commentid.");

  stdhead("Original comment");
  print("<h1>Original contents of comment #$commentid</h1><p>\n");
	print("<table width='500' border='1' cellspacing='0' cellpadding='5'>");
  print("<tr><td class='comment'>\n");
	echo htmlspecialchars($arr["ori_text"]);
  print("</td></tr></table>\n");

  $returnto = htmlspecialchars($_SERVER["HTTP_REFERER"]);

//	$returnto = "details.php?id=$torrentid&amp;viewcomm=$commentid#$commentid";

	if ($returnto)
 		print("<p><font size='small'>(<a href='$returnto'>back</a>)</font></p>\n");

	stdfoot();
	die;
}
else
	stderr("Error", "Unknown action");

die;
?>