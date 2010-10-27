<?php

if ( ! defined( 'IN_TBDEV_FORUM' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}



//-------- Action: Edit post

  if ($action == "editpost")
  {
    $postid = 0+$_GET["postid"];

    if (!is_valid_id($postid))
      die;

    $res = mysql_query("SELECT * FROM posts WHERE id=$postid") or sqlerr(__FILE__, __LINE__);

		if (mysql_num_rows($res) != 1)
			stderr("Error", "No post with ID.");

		$arr = mysql_fetch_assoc($res);

    $res2 = mysql_query("SELECT locked FROM topics WHERE id = " . $arr["topicid"]) or sqlerr(__FILE__, __LINE__);
		$arr2 = mysql_fetch_assoc($res2);

 		if (mysql_num_rows($res) != 1)
			stderr("Error", "No topic associated with post ID.");

		$locked = ($arr2["locked"] == 'yes');

    if (($CURUSER["id"] != $arr["userid"] || $locked) && get_user_class() < UC_MODERATOR)
      stderr("Error", "Denied!");

    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
    	$body = $_POST['body'];

    	if ($body == "")
    	  stderr("Error", "Body cannot be empty!");

      $body = sqlesc($body);

      $editedat = time();

      mysql_query("UPDATE posts SET body=$body, editedat=$editedat, editedby=$CURUSER[id] WHERE id=$postid") or sqlerr(__FILE__, __LINE__);

		$returnto = $_POST["returnto"];

			if ($returnto != "")
			{
				$returnto .= "&page=p$postid#$postid";
				header("Location: $returnto");
			}
			else
				stderr("Success", "Post was edited successfully.");
    }

    stdhead();

    print("<h1>Edit Post</h1>\n");

    print("<form method=post action=?action=editpost&postid=$postid>\n");
    print("<input type=hidden name=returnto value=\"" . htmlspecialchars($_SERVER["HTTP_REFERER"]) . "\">\n");

    print("<table border=1 cellspacing=0 cellpadding=5>\n");

    print("<tr><td style='padding: 0px'><textarea name=body cols=100 rows=20 style='border: 0px'>" . htmlspecialchars($arr["body"]) . "</textarea></td></tr>\n");

    print("<tr><td align=center><input type=submit value='Okay' class=btn></td></tr>\n");

    print("</table>\n");

    print("</form>\n");

    stdfoot();

  	die;
  }

  //-------- Action: Delete post

  if ($action == "deletepost")
  {
    $postid = 0+$_GET["postid"];

    $sure = $_GET["sure"];

    if (get_user_class() < UC_MODERATOR || !is_valid_id($postid))
      die;

    //------- Get topic id

    $res = mysql_query("SELECT topicid FROM posts WHERE id=$postid") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_row($res) or stderr("Error", "Post not found");

    $topicid = $arr[0];

    //------- We can not delete the post if it is the only one of the topic

    $res = mysql_query("SELECT COUNT(*) FROM posts WHERE topicid=$topicid") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_row($res);

    if ($arr[0] < 2)
      stderr("Error", "Can't delete post; it is the only post of the topic. You should\n" .
      "<a href=?action=deletetopic&topicid=$topicid&sure=1>delete the topic</a> instead.\n");


    //------- Get the id of the last post before the one we're deleting

    $res = mysql_query("SELECT id FROM posts WHERE topicid=$topicid AND id < $postid ORDER BY id DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);
		if (mysql_num_rows($res) == 0)
			$redirtopost = "";
		else
		{
			$arr = mysql_fetch_row($res);
			$redirtopost = "&page=p$arr[0]#$arr[0]";
		}

    //------- Make sure we know what we do :-)

    if (!$sure)
    {
      stderr("Delete post", "Sanity check: You are about to delete a post. Click\n" .
      "<a href=?action=deletepost&postid=$postid&sure=1>here</a> if you are sure.");
    }

    //------- Delete post

    mysql_query("DELETE FROM posts WHERE id=$postid") or sqlerr(__FILE__, __LINE__);

    //------- Update topic

    update_topic_last_post($topicid);

    header("Location: $BASEURL/forums.php?action=viewtopic&topicid=$topicid$redirtopost");

    die;
  }

?>