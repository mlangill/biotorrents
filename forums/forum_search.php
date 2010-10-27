<?php

if ( ! defined( 'IN_TBDEV_FORUM' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}



	stdhead("Forum Search");
	print("<h1>Forum Search (<font color=red>BETA</font>)</h1>\n");
	$keywords = isset($_GET["keywords"]) ? trim($_GET["keywords"]) : '';
	if ($keywords != "")
	{
		$perpage = 50;
		$page = max(1, 0 + isset($_GET["page"]) ? $_GET["page"] : 0);
		$ekeywords = sqlesc($keywords);
		print("<p><b>Searched for \"" . htmlspecialchars($keywords) . "\"</b></p>\n");
		$res = mysql_query("SELECT COUNT(*) FROM posts WHERE MATCH (body) AGAINST ($ekeywords)") or sqlerr(__FILE__, __LINE__);
		$arr = mysql_fetch_row($res);
		$hits = 0 + $arr[0];
		if ($hits == 0)
			print("<p><b>Sorry, nothing found!</b></p>");
		else
		{
			$pages = 0 + ceil($hits / $perpage);
			if ($page > $pages) $page = $pages;
			for ($i = 1; $i <= $pages; ++$i)
				if ($page == $i)
					$pagemenu1 .= "<font class=gray><b>$i</b></font>\n";
				else
					$pagemenu1 .= "<a href=\"forums?action=search&amp;keywords=" . htmlspecialchars($keywords) . "&amp;page=$i\"><b>$i</b></a>\n";
			if ($page == 1)
				$pagemenu2 = "<font class=gray><b>&lt;&lt; Prev</b></font>\n";
			else
				$pagemenu2 = "<a href=\"forums?action=search&amp;keywords=" . htmlspecialchars($keywords) . "&amp;page=" . ($page - 1) . "\"><b>&lt;&lt; Prev</b></a>\n";
			$pagemenu2 .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
			if ($page == $pages)
				$pagemenu2 .= "<font class=gray><b>Next &gt;&gt;</b></font>\n";
			else
				$pagemenu2 .= "<a href=\"forums?action=search&amp;keywords=" . htmlspecialchars($keywords) . "&amp;page=" . ($page + 1) . "\"><b>Next &gt;&gt;</b></a>\n";
			$offset = ($page * $perpage) - $perpage;
			$res = mysql_query("SELECT id, topicid,userid,added FROM posts WHERE MATCH (body) AGAINST ($ekeywords) LIMIT $offset,$perpage") or sqlerr(__FILE__, __LINE__);
			$num = mysql_num_rows($res);
			print("<p>$pagemenu1<br>$pagemenu2</p>");
			print("<table border=1 cellspacing=0 cellpadding=5>\n");
			print("<tr><td class=colhead>Post</td><td class=colhead align=left>Topic</td><td class=colhead align=left>Forum</td><td class=colhead align=left>Posted by</td></tr>\n");
			for ($i = 0; $i < $num; ++$i)
			{
				$post = mysql_fetch_assoc($res);
				$res2 = mysql_query("SELECT forumid, subject FROM topics WHERE id=$post[topicid]") or
					sqlerr(__FILE__, __LINE__);
				$topic = mysql_fetch_assoc($res2);
				$res2 = mysql_query("SELECT name,minclassread FROM forums WHERE id=$topic[forumid]") or
					sqlerr(__FILE__, __LINE__);
				$forum = mysql_fetch_assoc($res2);
				if ($forum["name"] == "" || $forum["minclassread"] > $CURUSER["class"])
				{
					--$hits;
					continue;
				}
				$res2 = mysql_query("SELECT username FROM users WHERE id=$post[userid]") or
					sqlerr(__FILE__, __LINE__);
				$user = mysql_fetch_assoc($res2);
				if ($user["username"] == "")
					$user["username"] = "[$post[userid]]";
				print("<tr><td>$post[id]</td><td align=left><a href=?action=viewtopic&amp;topicid=$post[topicid]&amp;page=p$post[id]#$post[id]><b>" . htmlspecialchars($topic["subject"]) . "</b></a></td><td align=left><a href=?action=viewforum&amp;forumid=$topic[forumid]><b>" . htmlspecialchars($forum["name"]) . "</b></a><td align=left><a href=userdetails.php?id=$post[userid]><b>$user[username]</b></a><br>at $post[added]</tr>\n");
			}
			print("</table>\n");
			print("<p>$pagemenu2<br>$pagemenu1</p>");
			print("<p>Found $hits post" . ($hits != 1 ? "s" : "") . ".</p>");
			print("<p><b>Search again</b></p>\n");
		}
	}
	print("<form method=get action=forums.php?>\n");
	print("<input type=hidden name=action value=search>\n");
	print("<table border=1 cellspacing=0 cellpadding=5>\n");
	print("<tr><td class=rowhead>Key words</td><td align=left><input type=text size=55 name=keywords value=\"" . htmlspecialchars($keywords) .
		 "\"><br>\n" .
		"<font class=small size=-1>Enter one or more words to search for.<br>Very common words and words with less than 3 characters are ignored.</font></td></tr>\n");
	print("<tr><td align=center colspan=2><input type=submit value='Search' class=btn></td></tr>\n");
	print("</table>\n</form>\n");
	stdfoot();
	die;
	?>