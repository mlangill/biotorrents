<?php

if ( ! defined( 'IN_TBDEV_FORUM' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}


  function catch_up()
  {
	//die("This feature is currently unavailable.");
    global $CURUSER, $READPOST_EXPIRY;

    $userid = $CURUSER["id"];

    //..rp..
$dt = (time() - $READPOST_EXPIRY);

$res = mysql_query(
"SELECT t.id, t.lastpost FROM topics AS t ".
"LEFT JOIN posts AS p ON p.id = t.lastpost ".
"WHERE p.added > $dt"
) or sqlerr(__FILE__, __LINE__);
//..rp..

    while ($arr = mysql_fetch_assoc($res))
    {
      $topicid = $arr["id"];

      $postid = $arr["lastpost"];

      $r = mysql_query("SELECT id,lastpostread FROM readposts WHERE userid=$userid and topicid=$topicid") or sqlerr(__FILE__, __LINE__);

      if (mysql_num_rows($r) == 0)
        mysql_query("INSERT INTO readposts (userid, topicid, lastpostread) VALUES($userid, $topicid, $postid)") or sqlerr(__FILE__, __LINE__);

      else
      {
        $a = mysql_fetch_assoc($r);

        if ($a["lastpostread"] < $postid)
          mysql_query("UPDATE readposts SET lastpostread=$postid WHERE id=" . $a["id"]) or sqlerr(__FILE__, __LINE__);
      }
    }
  }

  //-------- Returns the minimum read/write class levels of a forum

  function get_forum_access_levels($forumid)
  {
    $res = mysql_query("SELECT minclassread, minclasswrite, minclasscreate FROM forums WHERE id=$forumid") or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) != 1)
      return false;

    $arr = mysql_fetch_assoc($res);

    return array("read" => $arr["minclassread"], "write" => $arr["minclasswrite"], "create" => $arr["minclasscreate"]);
  }

  //-------- Returns the forum ID of a topic, or false on error

  function get_topic_forum($topicid)
  {
    $res = mysql_query("SELECT forumid FROM topics WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) != 1)
      return false;

    $arr = mysql_fetch_row($res);

    return $arr[0];
  }

  //-------- Returns the ID of the last post of a forum

  function update_topic_last_post($topicid)
  {
    $res = mysql_query("SELECT id FROM posts WHERE topicid=$topicid ORDER BY id DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_row($res) or die("No post found");

    $postid = $arr[0];

    mysql_query("UPDATE topics SET lastpost=$postid WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);
  }

  function get_forum_last_post($forumid)
  {
    $res = mysql_query("SELECT lastpost FROM topics WHERE forumid=$forumid ORDER BY lastpost DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_row($res);

    $postid = $arr[0];

    if ($postid)
      return $postid;

    else
      return 0;
  }

  //-------- Inserts a quick jump menu

  function insert_quick_jump_menu($currentforum = 0)
  {
    print("<p align=center><form method=get action=? name=jump>\n");

    print("<input type=hidden name=action value=viewforum>\n");

    print("Quick jump: ");

    print("<select name=forumid onchange=\"if(this.options[this.selectedIndex].value != -1){ forms['jump'].submit() }\">\n");

    $res = mysql_query("SELECT * FROM forums ORDER BY name") or sqlerr(__FILE__, __LINE__);

    while ($arr = mysql_fetch_assoc($res))
    {
      if (get_user_class() >= $arr["minclassread"])
        print("<option value=" . $arr["id"] . ($currentforum == $arr["id"] ? " selected>" : ">") . $arr["name"] . "\n");
    }

    print("</select>\n");

    print("<input type=submit value='Go!'>\n");

    print("</form>\n</p>");
  }

  //-------- Inserts a compose frame

  function insert_compose_frame($id, $newtopic = true, $quote = false)
  {
    global $maxsubjectlength, $CURUSER, $forum_pic_url;


    if ($newtopic)
    {
      $res = mysql_query("SELECT name FROM forums WHERE id=$id") or sqlerr(__FILE__, __LINE__);

      $arr = mysql_fetch_assoc($res) or die("Bad forum id");

      $forumname = $arr["name"];

      print("<p align=center>New topic in <a href=?action=viewforum&forumid=$id>$forumname</a> forum</p>\n");
    }
    else
    {
      $res = mysql_query("SELECT * FROM topics WHERE id=$id") or sqlerr(__FILE__, __LINE__);

      $arr = mysql_fetch_assoc($res) or stderr("Forum error", "Topic not found.");

      $subject = htmlentities($arr["subject"], ENT_QUOTES);

      print("<p align=center>Reply to topic: <a href=?action=viewtopic&topicid=$id>$subject</a></p>");
    }

    begin_frame("Compose", true);

    print("<form method=post action=forums.php?action=post>\n");

    if ($newtopic)
      print("<input type=hidden name=forumid value=$id>\n");

    else
      print("<input type=hidden name=topicid value=$id>\n");

    begin_table();

    if ($newtopic)
      print("<tr><td class=rowhead>Subject</td>" .
        "<td align=left style='padding: 0px'><input type=text size=100 maxlength=$maxsubjectlength name=subject " .
        "style='border: 0px; height: 19px'></td></tr>\n");

    if ($quote)
    {
       $postid = (int)$_GET["postid"];
       if (!is_valid_id($postid))
         header("Location: $BASEURL/forums.php");

	   $res = mysql_query("SELECT posts.*, users.username FROM posts LEFT JOIN users ON posts.userid = users.id WHERE posts.id=$postid") or sqlerr(__FILE__, __LINE__);

	   if (mysql_num_rows($res) != 1)
	     stderr("Error", "No post with ID.");

	   $arr = mysql_fetch_assoc($res);
    }

    print("<tr><td class=rowhead>Body</td><td align=left style='padding: 0px'>" .
    "<textarea name=body cols=100 rows=20 style='border: 0px'>".
    ($quote?(("[quote=".htmlspecialchars($arr["username"])."]".htmlspecialchars($arr["body"])."[/quote]")):"").
    "</textarea></td></tr>\n");

    print("<tr><td colspan=2 align=center><input type=submit class=btn value='Submit'></td></tr>\n");

    end_table();

    print("</form>\n");

		print("<p align=center><a href=tags.php target=_blank>Tags</a> | <a href=smilies.php target=_blank>Smilies</a></p>\n");

    end_frame();

    //------ Get 10 last posts if this is a reply

    if (!$newtopic)
    {
      $postres = mysql_query("SELECT * FROM posts WHERE topicid=$id ORDER BY id DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);

      begin_frame("10 last posts, in reverse order");

      while ($post = mysql_fetch_assoc($postres))
      {
        //-- Get poster details

        $userres = mysql_query("SELECT * FROM users WHERE id=" . $post["userid"] . " LIMIT 1") or sqlerr(__FILE__, __LINE__);

        $user = mysql_fetch_assoc($userres);

      	if ($CURUSER["avatars"] == "yes")
          {
            $avatar = $user['avatar'] ? "<img width={$user['av_w']} height={$user['av_h']} src='".htmlentities($user['avatar'], ENT_QUOTES)."' />" : "<img width=100 src='{$forum_pic_url}default_avatar.gif' />";
          }
          else
            $avatar = "<img width=100 src='{$forum_pic_url}default_avatar.gif' />";

        print("<p class=sub>#" . $post["id"] . " by " . $user["username"] . " on " . get_date( $post['added'],''));

        begin_table(true);

        print("<tr valign=top><td width=150 align=center style='padding: 0px'>" . ($avatar ? $avatar : "").
          "</td><td class=comment>" . format_comment($post["body"]) . "</td></tr>\n");

        end_table();

      }

      end_frame();

    }

  insert_quick_jump_menu();

  }
?>