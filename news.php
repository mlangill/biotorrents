<?php

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/bbcode_functions.php";
require_once "include/html_functions.php";

dbconn();
loggedinorreturn();

if ($CURUSER['class'] < UC_ADMINISTRATOR)
	stderr("Error", "Permission denied.");

$action = isset($_GET["action"]) ? $_GET['action'] : '';
$warning = '';
//   Delete News Item    //////////////////////////////////////////////////////

if ($action == 'delete')
{
	$newsid = (int)$_GET["newsid"];
  if (!is_valid_id($newsid))
  	stderr("Error","Invalid news item ID - Code 1.");

  $returnto = htmlentities($_GET["returnto"]);

  $sure = isset($_GET["sure"]) ? (int)$_GET['sure'] : 0;
  if (!$sure)
    stderr("Delete news item","Do you really want to delete a news item? Click\n" .
    	"<a href='?action=delete&amp;newsid=$newsid&amp;returnto=news&amp;sure=1'>here</a> if you are sure.");

  mysql_query("DELETE FROM news WHERE id=$newsid") or sqlerr(__FILE__, __LINE__);

	if ($returnto != "")
		header("Location: $BASEURL/news.php");
	else
		$warning = "News item was deleted successfully.";
}

//   Add News Item    /////////////////////////////////////////////////////////

if ($action == 'add')
{

	$body = $_POST["body"];
	if (!$body)
		stderr("Error","The news item cannot be empty!");

	$added = isset($_POST["added"]) ? $_POST['added'] : 0;
	if (!$added)
		$added = time();

  mysql_query("INSERT INTO news (userid, added, body) VALUES (".
  	$CURUSER['id'] . ", $added, " . sqlesc($body) . ")") or sqlerr(__FILE__, __LINE__);
	if (mysql_affected_rows() == 1)
		$warning = "News item was added successfully.";
	else
		stderr("Error","Something weird just happened.");
}

//   Edit News Item    ////////////////////////////////////////////////////////

if ($action == 'edit')
{

	$newsid = isset( $_GET["newsid"]) ? (int)$_GET["newsid"] : '';

  if (!is_valid_id($newsid))
  	stderr("Error","Invalid news item ID - Code 2.");

  $res = mysql_query("SELECT * FROM news WHERE id=$newsid") or sqlerr(__FILE__, __LINE__);

	if (mysql_num_rows($res) != 1)
	  stderr("Error", "No news item with ID.");

	$arr = mysql_fetch_assoc($res);

  if ($_SERVER['REQUEST_METHOD'] == 'POST')
  {
  	$body = $_POST['body'];

    if ($body == "")
    	stderr("Error", "Body cannot be empty!");

    $body = sqlesc($body);

    $editedat = time();

    mysql_query("UPDATE news SET body=$body WHERE id=$newsid") or sqlerr(__FILE__, __LINE__);

    $returnto = isset($_POST['returnto']) ? htmlentities($_POST['returnto']) : '';

		if ($returnto != "")
			header("Location: $BASEURL/news.php");
		else
			$warning = "News item was edited successfully.";
  }
  else
  {
 	 	//$returnto = isset($_GET['returnto']) ? htmlentities($_GET['returnto']) : $BASEURL.'/news.php';
	  stdhead();
	  print("<h1>Edit News Item</h1>\n");
	  print("<form method='post' action='news.php?action=edit&amp;newsid=$newsid'>\n");
	  print("<table border='1' cellspacing='0' cellpadding='5'>\n");
	  //print("<tr><td><input type='hidden' name='returnto' value='$returnto' /></td></tr>\n");
	  print("<tr><td style='padding: 0px'><textarea name='body' cols='145' rows='5' style='border: 0px'>" . htmlspecialchars($arr["body"]) . "</textarea></td></tr>\n");
	  print("<tr><td align='center'><input type='submit' value='Okay' class='btn' /></td></tr>\n");
	  print("</table>\n");
	  print("</form>\n");
	  stdfoot();
	  die;
  }
}

//   Other Actions and followup    ////////////////////////////////////////////

stdhead("Site news");
print("<h1>Submit News Item</h1>\n");
if (!empty($warning))
	print("<p><font size='-3'>($warning)</font></p>");
print("<form method='post' action='?action=add'>\n");
print("<table border='1' cellspacing='0' cellpadding='5'>\n");
print("<tr><td style='padding: 10px'><textarea name='body' cols='141' rows='5' style='border: 0px'></textarea>\n");
print("<br /><br /><div align='center'><input type='submit' value='Okay' class='btn' /></div></td></tr>\n");
print("</table></form><br /><br />\n");

$res = mysql_query("SELECT * FROM news ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);

if (mysql_num_rows($res) > 0)
{


 	begin_main_frame();
	begin_frame();

	while ($arr = mysql_fetch_assoc($res))
	{
		$newsid = $arr["id"];
		$body = format_comment($arr["body"]);
	  $userid = $arr["userid"];
	  $added = get_date( $arr['added'],'');

    $res2 = mysql_query("SELECT username, donor FROM users WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
    $arr2 = mysql_fetch_assoc($res2);

    $postername = $arr2["username"];

    if ($postername == "")
    	$by = "unknown[$userid]";
    else
    	$by = "<a href='userdetails.php?id=$userid'><b>$postername</b></a>" .
    		($arr2["donor"] == "yes" ? "<img src=\"{$pic_base_url}star.gif\" alt='Donor' />" : "");

	  print("<div class='sub'><table border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>");
    print("$added&nbsp;---&nbsp;by&nbsp$by");
    print(" - [<a href='?action=edit&amp;newsid=$newsid'><b>Edit</b></a>]");
    print(" - [<a href='?action=delete&amp;newsid=$newsid'><b>Delete</b></a>]");
    print("</td></tr></table></div>\n");

	  begin_table(true);
	  print("<tr valign='top'><td class='comment'>$body</td></tr>\n");
	  end_table();
	}
	end_frame();
	end_main_frame();
}
else
  stdmsg("Sorry", "No news available!");
stdfoot();
die;
?>