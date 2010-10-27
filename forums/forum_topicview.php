<?php

if ( ! defined( 'IN_TBDEV_FORUM' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}




    $topicid = (int)$_GET["topicid"];

    $page = isset($_GET["page"]) ? (int)$_GET["page"] : false;

    if (!is_valid_id($topicid))
      die;

    $userid = $CURUSER["id"];

    //------ Get topic info

    $res = mysql_query("SELECT * FROM topics WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_assoc($res) or stderr("Forum error", "Topic not found");

    $locked = ($arr["locked"] == 'yes');
    $subject = htmlentities($arr["subject"], ENT_QUOTES);
    $sticky = $arr["sticky"] == "yes";
    $forumid = $arr["forumid"];

	//------ Update hits column

    mysql_query("UPDATE topics SET views = views + 1 WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    //------ Get forum

    $res = mysql_query("SELECT * FROM forums WHERE id=$forumid") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_assoc($res) or die("Forum = NULL");

    $forum = $arr["name"];

    if ($CURUSER["class"] < $arr["minclassread"])
		stderr("Error", "You are not permitted to view this topic.");

    //------ Get post count

    $res = mysql_query("SELECT COUNT(*) FROM posts WHERE topicid=$topicid") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_row($res);

    $postcount = $arr[0];

    //------ Make page menu

    $pagemenu = "<p>\n";

    $perpage = $postsperpage;

    $pages = ceil($postcount / $perpage);

    if ($page[0] == "p")
  	{
	    $findpost = substr($page, 1);
	    $res = mysql_query("SELECT id FROM posts WHERE topicid=$topicid ORDER BY added") or sqlerr(__FILE__, __LINE__);
	    $i = 1;
	    while ($arr = mysql_fetch_row($res))
	    {
	      if ($arr[0] == $findpost)
	        break;
	      ++$i;
	    }
	    $page = ceil($i / $perpage);
	  }

    if ($page == "last")
      $page = $pages;
    else
    {
      if($page < 1)
        $page = 1;
      elseif ($page > $pages)
        $page = $pages;
    }

    $offset = $page * $perpage - $perpage;

    for ($i = 1; $i <= $pages; ++$i)
    {
      if ($i == $page)
        $pagemenu .= "<font class=gray><b>$i</b></font>\n";

      else
        $pagemenu .= "<a href=?action=viewtopic&topicid=$topicid&page=$i><b>$i</b></a>\n";
    }

    if ($page == 1)
      $pagemenu .= "<br><font class=gray><b>&lt;&lt; Prev</b></font>";

    else
      $pagemenu .= "<br><a href=?action=viewtopic&topicid=$topicid&page=" . ($page - 1) .
        "><b>&lt;&lt; Prev</b></a>";

    $pagemenu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    if ($page == $pages)
      $pagemenu .= "<font class=gray><b>Next &gt;&gt;</b></font></p>\n";

    else
      $pagemenu .= "<a href=?action=viewtopic&topicid=$topicid&page=" . ($page + 1) .
        "><b>Next &gt;&gt;</b></a></p>\n";

    //------ Get posts

    $res = mysql_query("SELECT p. * , u.username, u.class, u.avatar, u.av_w, u.av_h, 
						u.donor, u.title, u.enabled, u.warned, u.reputation 
						FROM posts p
						LEFT JOIN users u ON u.id = p.userid
						WHERE topicid = $topicid ORDER BY p.id LIMIT $offset,$perpage") or sqlerr(__FILE__, __LINE__);

    
    
    
    stdhead("View topic");
	
	echo "<script type='text/javascript' src='./scripts/popup.js'></script>";
	
    print("<a name=top><h1><a href=?action=viewforum&forumid=$forumid>$forum</a> &gt; $subject</h1>\n");

    print($pagemenu);

    //------ Print table

    begin_main_frame();

    begin_frame();

    $pc = mysql_num_rows($res);

    $pn = 0;

    $r = mysql_query("SELECT lastpostread FROM readposts WHERE userid=" . $CURUSER["id"] . " AND topicid=$topicid") or sqlerr(__FILE__, __LINE__);

    $a = mysql_fetch_row($r);

    $lpr = $a[0];

    //..rp..
/* if (!$lpr)
mysql_query("INSERT INTO readposts (userid, topicid) VALUES($userid, $topicid)") or sqlerr(__FILE__, __LINE__);
*/
//..rp..

    while ($arr = mysql_fetch_assoc($res))
    {
      ++$pn;

      $postid = $arr["id"];

      $posterid = $arr["userid"];

      $added = get_date( $arr['added'],'');

      //---- Get poster details

      //$res2 = mysql_query("SELECT username, class, avatar, av_w, av_h, donor, title, enabled, warned FROM users WHERE id=$posterid") or sqlerr(__FILE__, __LINE__);

      //$arr2 = mysql_fetch_assoc($res2);

      $postername = $arr["username"];

      if ($postername == "")
      {
        $by = "unknown[$posterid]";

        //$avatar = "";
      }
      else
      {
//		if ($arr2["enabled"] == "yes")
	        //$avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($arr2["avatar"]) : "");
//	    else
//			$avatar = "{$pic_base_url}disabled_avatar.gif";

        $title = $arr["title"];

        if (!$title)
          $title = get_user_class_name($arr["class"]);

        $by = "<a href=userdetails.php?id=$posterid><b>$postername</b></a>" . ($arr["donor"] == "yes" ? "<img src=".
        "\"{$pic_base_url}star.gif\" alt='Donor'>" : "") . ($arr["enabled"] == "no" ? "<img src=\"".
        "\"{$pic_base_url}disabled.gif\" alt=\"This account is disabled\" style='margin-left: 2px'>" : ($arr["warned"] == "yes" ? "<a href=rules.php#warning class=altlink><img src=\"{$pic_base_url}warned.gif\" alt=\"Warned\" border=0></a>" : "")) . " ($title)";
      }

      if ($CURUSER["avatars"] == "yes")
          {
            //$avatar = $arr['avatar'] ? "<img width={$arr['av_w']} height={$arr['av_h']} src='".htmlentities($arr['avatar'], ENT_QUOTES)."' />" : "<img width=100 src='{$forum_pic_url}default_avatar.gif' />";
          $avatar = $arr['avatar'] ? "<img src='".htmlentities($arr['avatar'], ENT_QUOTES)."' />" : "<img width=100 src='{$forum_pic_url}default_avatar.gif' />";
	  }
      else
            $avatar = "<img width=100 src='{$forum_pic_url}default_avatar.gif' />";

      print("<a name=$postid>\n");

      if ($pn == $pc)
      {
        print("<a name=last>\n");
        //..rp..
/* if ($postid > $lpr)
mysql_query("UPDATE readposts SET lastpostread=$postid WHERE userid=$userid AND topicid=$topicid") or sqlerr(__FILE__, __LINE__);
*/
//..rp..
      }

      print("<p class=sub><table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded width=99%>#$postid by $by at $added");

      if (!$locked || get_user_class() >= UC_MODERATOR)
				print(" - [<a href=?action=quotepost&topicid=$topicid&postid=$postid><b>Quote</b></a>]");

      if (($CURUSER["id"] == $posterid && !$locked) || get_user_class() >= UC_MODERATOR)
        print(" - [<a href=?action=editpost&postid=$postid><b>Edit</b></a>]");

      if (get_user_class() >= UC_MODERATOR)
        print(" - [<a href=?action=deletepost&postid=$postid><b>Delete</b></a>]");

      print("</td><td class=embedded width=1%><a href=#top><img src=\"{$forum_pic_url}top.gif\" border=0 alt='Top'></a></td></tr>");

      print("</table></p>\n");

      begin_table(true);

      $body = format_comment($arr["body"]);

      if (is_valid_id($arr['editedby']))
      {
        $res2 = mysql_query("SELECT username FROM users WHERE id=$arr[editedby]");
        if (mysql_num_rows($res2) == 1)
        {
          $arr2 = mysql_fetch_assoc($res2);
          $body .= "<p><font size=1 class=small>Last edited by <a href=userdetails.php?id={$arr['editedby']}><b>{$arr2['username']}</b></a> on ".get_date( $arr['editedat'],'')."</font></p>\n";
        }
      }

#	Removed reputation from forum
#		$member_reputation = $arr['username'] != '' ? get_reputation($arr) : '';
#      print("<tr valign=top><td width=150 align=center style='padding: 0px'>" .
#       ($avatar ? $avatar : ""). "<br /><div>$member_reputation</div></td><td class=comment>$body</td></tr>\n");
      
      print("<tr valign=top><td width=150 align=center style='padding: 0px'>" .
        ($avatar ? $avatar : ""). "<br /></td><td class=comment>$body</td></tr>\n");

      end_table();
    
    $postadd = $arr['added'];
	//..rp..
	if (($postid > $lpr) AND ($postadd > (time() - $READPOST_EXPIRY))) {
	
	if ($lpr)
	mysql_query("UPDATE readposts SET lastpostread=$postid ".
	"WHERE userid=$userid AND topicid=$topicid") or sqlerr(__FILE__, __LINE__);
	else
	mysql_query("INSERT INTO readposts (userid, topicid, lastpostread) ".
	"VALUES($userid, $topicid, $postid)") or sqlerr(__FILE__, __LINE__);
	
	}}
	//..rp..
	
	
    //------ Mod options

	  if (get_user_class() >= UC_MODERATOR)
	  {
	    attach_frame();

	    $res = mysql_query("SELECT id,name,minclasswrite FROM forums ORDER BY name") or sqlerr(__FILE__, __LINE__);
	    print("<table border=0 cellspacing=0 cellpadding=0>\n");

	    print("<form method=post action=forums.php?action=setsticky>\n");
	    print("<input type=hidden name=topicid value=$topicid>\n");
	    print("<input type=hidden name=returnto value=$_SERVER[REQUEST_URI]>\n");
	    print("<tr><td class=embedded align=right>Sticky:</td>\n");
	    print("<td class=embedded><input type=radio name=sticky value='yes' " . ($sticky ? " checked" : "") . "> Yes <input type=radio name=sticky value='no' " . (!$sticky ? " checked" : "") . "> No\n");
	    print("<input type=submit value='Set'></td></tr>");
	    print("</form>\n");

	    print("<form method=post action=forums.php?action=setlocked>\n");
	    print("<input type=hidden name=topicid value=$topicid>\n");
	    print("<input type=hidden name=returnto value=$_SERVER[REQUEST_URI]>\n");
	    print("<tr><td class=embedded align=right>Locked:</td>\n");
	    print("<td class=embedded><input type=radio name=locked value='yes' " . ($locked ? " checked" : "") . "> Yes <input type=radio name=locked value='no' " . (!$locked ? " checked" : "") . "> No\n");
	    print("<input type=submit value='Set'></td></tr>");
	    print("</form>\n");

	    print("<form method=post action=forums.php?action=renametopic>\n");
	    print("<input type=hidden name=topicid value=$topicid>\n");
	    print("<input type=hidden name=returnto value=$_SERVER[REQUEST_URI]>\n");
	    print("<tr><td class=embedded align=right>Rename topic:</td><td class=embedded><input type=text name=subject size=60 maxlength=$maxsubjectlength value=\"" . htmlspecialchars($subject) . "\">\n");
	    print("<input type=submit value='Okay'></td></tr>");
	    print("</form>\n");

	    print("<form method=post action=forums.php?action=movetopic&topicid=$topicid>\n");
	    print("<tr><td class=embedded>Move this thread to:&nbsp;</td><td class=embedded><select name=forumid>");

	    while ($arr = mysql_fetch_assoc($res))
	      if ($arr["id"] != $forumid && get_user_class() >= $arr["minclasswrite"])
	        print("<option value=" . $arr["id"] . ">" . $arr["name"] . "\n");

	    print("</select> <input type=submit value='Okay'></form></td></tr>\n");
	    print("<tr><td class=embedded>Delete topic</td><td class=embedded>\n");
	    print("<form method=get action=forums.php>\n");
	    print("<input type=hidden name=action value=deletetopic>\n");
	    print("<input type=hidden name=topicid value=$topicid>\n");
	    print("<input type=hidden name=forumid value=$forumid>\n");
	    print("<input type=checkbox name=sure value=1>I'm sure\n");
	    print("<input type=submit value='Okay'>\n");
	    print("</form>\n");
	    print("</td></tr>\n");
	    print("</table>\n");
	  }

  	end_frame();

  	end_main_frame();

  	print($pagemenu);

  	if ($locked && get_user_class() < UC_MODERATOR)
  		print("<p>This topic is locked; no new posts are allowed.</p>\n");

  	else
  	{
	    $arr = get_forum_access_levels($forumid) or die;

	    if (get_user_class() < $arr["write"])
	      print("<p><i>You are not permitted to post in this forum.</i></p>\n");

	    else
	      $maypost = true;
	  }

	  //------ "View unread" / "Add reply" buttons

	  print("<p><table class=main border=0 cellspacing=0 cellpadding=0><tr>\n");
	  print("<td class=embedded><form method=get action=?>\n");
	  print("<input type=hidden name=action value=viewunread>\n");
	  print("<input type=submit value='View Unread' class=btn>\n");
	  print("</form></td>\n");

    if ($maypost)
    {
      print("<td class=embedded style='padding-left: 10px'><form method=get action=?>\n");
      print("<input type=hidden name=action value=reply>\n");
      print("<input type=hidden name=topicid value=$topicid>\n");
      print("<input type=submit value='Add Reply' class=btn>\n");
      print("</form></td>\n");
    }
    print("</tr></table></p>\n");

    //------ Forum quick jump drop-down

    insert_quick_jump_menu($forumid);

    stdfoot();

    die;
?>