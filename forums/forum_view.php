<?php

if ( ! defined( 'IN_TBDEV_FORUM' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}


  //-------- Action: View forum


    $forumid = (int)$_GET["forumid"];

    if (!is_valid_id($forumid))
      header("Location: $BASEURL/forum.php");

    $page = isset($_GET["page"]) ? (int)$_GET["page"] : 0;

    $userid = $CURUSER["id"];

    //------ Get forum name

    $res = mysql_query("SELECT name, minclassread FROM forums WHERE id=$forumid") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_assoc($res) or die;

    $forumname = $arr["name"];

    if (get_user_class() < $arr["minclassread"])
      header("Location: $BASEURL/forum.php");
      //die("Not permitted");

    //------ Page links

/////////////////// Get topic count & Do Pager thang! ////////////////////////////

    $perpage = $CURUSER["topicsperpage"];
	if (!$perpage) $perpage = 20;

    $res = mysql_query("SELECT COUNT(*) FROM topics WHERE forumid=$forumid") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_row($res);

    $num = $arr[0];

    if ($page == 0)
      $page = 1;

    $first = ($page * $perpage) - $perpage + 1;

    $last = $first + $perpage - 1;

    if ($last > $num)
      $last = $num;

    $pages = floor($num / $perpage);

    if ($perpage * $pages < $num)
      ++$pages;

    //------ Build menu

    $menu = "<p align=center><b>\n";

    $lastspace = false;

    for ($i = 1; $i <= $pages; ++$i)
    {
    	if ($i == $page)
        $menu .= "<font class=gray>$i</font>\n";

      elseif ($i > 3 && ($i < $pages - 2) && ($page - $i > 3 || $i - $page > 3))
    	{
    		if ($lastspace)
    		  continue;

  		  $menu .= "... \n";

     		$lastspace = true;
    	}

      else
      {
        $menu .= "<a href=?action=viewforum&forumid=$forumid&page=$i>$i</a>\n";

        $lastspace = false;
      }
      if ($i < $pages)
        $menu .= "</b>|<b>\n";
    }

    $menu .= "<br>\n";

    if ($page == 1)
      $menu .= "<font class=gray>&lt;&lt; Prev</font>";

    else
      $menu .= "<a href=?action=viewforum&forumid=$forumid&page=" . ($page - 1) . ">&lt;&lt; Prev</a>";

    $menu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    if ($last == $num)
      $menu .= "<font class=gray>Next &gt;&gt;</font>";

    else
      $menu .= "<a href=?action=viewforum&forumid=$forumid&page=" . ($page + 1) . ">Next &gt;&gt;</a>";

    $menu .= "</b></p>\n";

    $offset = $first - 1;
/////////////////// Get topic count & Do Pager thang end! ////////////////////////////

    //------ Get topics data

    $topicsres = mysql_query("SELECT * FROM topics WHERE forumid=$forumid ORDER BY sticky, lastpost DESC LIMIT $offset,$perpage") or sqlerr(__FILE__, __LINE__);

    stdhead("Forum :: Forum View");

    $numtopics = mysql_num_rows($topicsres);

    $htmlout = "<h1>$forumname</h1>\n";

    if ($numtopics > 0)
    {
      $htmlout .= $menu;

      $htmlout .= "<table border='1' cellspacing='0' cellpadding='5' width='80%'>";

      $htmlout .= "<tr><td class=colhead align=left>Topic</td><td class=colhead>Replies</td><td class=colhead>Views</td>\n" .
        "<td class=colhead align=left>Author</td><td class=colhead align=left>Last&nbsp;post</td>\n";

      $htmlout .= "</tr>\n";

      while ($topicarr = mysql_fetch_assoc($topicsres))
      {
        $topicid = $topicarr["id"];

        $topic_userid = $topicarr["userid"];

        $topic_views = $topicarr["views"];

        $views = number_format($topic_views);

        $locked = $topicarr["locked"] == "yes";

        $sticky = $topicarr["sticky"] == "yes";

        //---- Get reply count

        $res = mysql_query("SELECT COUNT(*) FROM posts WHERE topicid=$topicid") or sqlerr(__FILE__, __LINE__);

        $arr = mysql_fetch_row($res);

        $posts = $arr[0];

        $replies = max(0, $posts - 1);

        $tpages = floor($posts / $postsperpage);

        if ($tpages * $postsperpage != $posts)
          ++$tpages;

        if ($tpages > 1)
        {
          $topicpages = " (<img src=\"{$forum_pic_url}multipage.gif\">";

          for ($i = 1; $i <= $tpages; ++$i)
            $topicpages .= " <a href=?action=viewtopic&topicid=$topicid&page=$i>$i</a>";

          $topicpages .= ")";
        }
        else
          $topicpages = "";

        //---- Get userID and date of last post

        $res = mysql_query("SELECT * FROM posts WHERE topicid=$topicid ORDER BY id DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);

        $arr = mysql_fetch_assoc($res);

        $lppostid = 0 + $arr["id"];
		
		//..rp..
		$lppostadd = $arr["added"];
		// ..rp..
		
        $lpuserid = 0 + $arr["userid"];

        $lpadded = "<nobr>" . get_date( $arr['added'],'') . "</nobr>";

        //------ Get name of last poster

        $res = mysql_query("SELECT * FROM users WHERE id=$lpuserid") or sqlerr(__FILE__, __LINE__);

        if (mysql_num_rows($res) == 1)
        {
          $arr = mysql_fetch_assoc($res);

          $lpusername = "<a href=userdetails.php?id=$lpuserid><b>$arr[username]</b></a>";
        }
        else
          $lpusername = "unknown[$topic_userid]";

        //------ Get author

        $res = mysql_query("SELECT username FROM users WHERE id=$topic_userid") or sqlerr(__FILE__, __LINE__);

        if (mysql_num_rows($res) == 1)
        {
          $arr = mysql_fetch_assoc($res);

          $lpauthor = "<a href=userdetails.php?id=$topic_userid><b>$arr[username]</b></a>";
        }
        else
          $lpauthor = "unknown[$topic_userid]";

        //---- Print row

        $r = mysql_query("SELECT lastpostread FROM readposts WHERE userid=$userid AND topicid=$topicid") or sqlerr(__FILE__, __LINE__);

        $a = mysql_fetch_row($r);

        $new = !$a || $lppostid > $a[0];

		// ..rp..
		$new = ($lppostadd > (time() - $READPOST_EXPIRY)) ? (!$a || $lppostid > $a[0]) : 0;
		//..rp..

        $topicpic = ($locked ? ($new ? "lockednew" : "locked") : ($new ? "unlockednew" : "unlocked"));

        $subject = ($sticky ? "Sticky: " : "") . "<a href=?action=viewtopic&topicid=$topicid><b>" .
        htmlentities($topicarr["subject"], ENT_QUOTES) . "</b></a>$topicpages";

        $htmlout .= "<tr><td align=left><table border=0 cellspacing=0 cellpadding=0><tr>" .
        "<td class=embedded style='padding-right: 5px'><img src=\"{$forum_pic_url}{$topicpic}.gif\">" .
        "</td><td class=embedded align=left>\n" .
        "$subject</td></tr></table></td><td align=right>$replies</td>\n" .
        "<td align=right>$views</td><td align=left>$lpauthor</td>\n" .
        "<td align=left>$lpadded<br>by&nbsp;$lpusername</td>\n";

        $htmlout .= "</tr>\n";
      } // while

      $htmlout .= "</table>\n";

      $htmlout .= $menu;

    } // if
    else
      print("<p align=center>No topics found</p>\n");

    $htmlout .= "<p><table class=main border=0 cellspacing=0 cellpadding=0><tr valign=center>\n";

    $htmlout .= "<td class=embedded><img src=\"{$forum_pic_url}unlockednew.gif\" style='margin-right: 5px'></td><td class=embedded>New posts</td>\n";

    $htmlout .= "<td class=embedded><img src=\"{$forum_pic_url}locked.gif\" style='margin-left: 10px; margin-right: 5px'>" .
    "</td><td class=embedded>Locked topic</td>\n";

    $htmlout .= "</tr></table></p>\n";

    $arr = get_forum_access_levels($forumid) or die;

    $maypost = get_user_class() >= $arr["write"] && get_user_class() >= $arr["create"];

    if (!$maypost)
      $htmlout .= "<p><i>You are not permitted to start new topics in this forum.</i></p>\n";

    $htmlout .= "<p><table border=0 class=main cellspacing=0 cellpadding=0><tr>\n";

    $htmlout .= "<td class=embedded><form method=get action=?><input type=hidden " .
    "name=action value=viewunread><input type=submit value='View unread' class=btn></form></td>\n";

    if ($maypost)
      $htmlout .= "<td class=embedded><form method=get action=?><input type=hidden " .
      "name=action value=newtopic><input type=hidden name=forumid " .
      "value=$forumid><input type=submit value='New topic' class=btn style='margin-left: 10px'></form></td>\n";

    $htmlout .= "</tr></table></p>\n";
    
    echo $htmlout;

    insert_quick_jump_menu($forumid);

    stdfoot();

    die;

?>