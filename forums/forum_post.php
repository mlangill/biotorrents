<?php

if ( ! defined( 'IN_TBDEV_FORUM' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}



    $forumid = isset($_POST["forumid"]) ? (int)$_POST["forumid"] : 0;
    $topicid = isset($_POST["topicid"]) ? (int)$_POST["topicid"] : 0;

    if (!is_valid_id($forumid) && !is_valid_id($topicid))
      stderr("Error", "Bad forum or topic ID.");

    $newtopic = $forumid > 0;

    

    if ($newtopic)
    {
      $subject = trim(strip_tags($_POST["subject"]));

      if (!$subject)
        stderr("Error", "You must enter a subject.");

      if (strlen($subject) > $maxsubjectlength)
        stderr("Error", "Subject is limited to $maxsubjectlength characters.");
    }
    else
      $forumid = get_topic_forum($topicid) or die("Bad topic ID");

    //------ Make sure sure user has write access in forum

    $arr = get_forum_access_levels($forumid) or die("Bad forum ID");

    if (get_user_class() < $arr["write"] || ($newtopic && get_user_class() < $arr["create"]))
      stderr("Error", "Permission denied.");

    $body = trim($_POST["body"]);

    if ($body == "")
      stderr("Error", "No body text.");

    $userid = $CURUSER["id"];

    if ($newtopic)
    {
      //---- Create topic

      $subject = sqlesc($subject);

      mysql_query("INSERT INTO topics (userid, forumid, subject) VALUES($userid, $forumid, $subject)") or sqlerr(__FILE__, __LINE__);

      $topicid = mysql_insert_id() or stderr("Error", "No topic ID returned");
    }
    else
    {
      //---- Make sure topic exists and is unlocked

      $res = mysql_query("SELECT * FROM topics WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

      $arr = mysql_fetch_assoc($res) or die("Topic id n/a");

      if ($arr["locked"] == 'yes' && get_user_class() < UC_MODERATOR)
        stderr("Error", "This topic is locked.");

      //---- Get forum ID

      $forumid = $arr["forumid"];
    }

    //------ Insert post

    $added = time();

    $body = sqlesc($body);

    mysql_query("INSERT INTO posts (topicid, userid, added, body) " .
    "VALUES($topicid, $userid, $added, $body)") or sqlerr(__FILE__, __LINE__);

    $postid = mysql_insert_id() or die("Post id n/a");

    //------ Update topic last post

    update_topic_last_post($topicid);

    //------ All done, redirect user to the post

    $headerstr = "Location: $BASEURL/forums.php?action=viewtopic&topicid=$topicid&page=last";

    if ($newtopic)
      header($headerstr);

    else
      header("$headerstr#$postid");

    die;
?>