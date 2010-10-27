<?php

function linkcolor($num) {
    if (!$num)
        return "red";
//    if ($num == 1)
//        return "yellow";
    return "green";
}

function torrenttable($res, $variant = "index") {
	global $pic_base_url, $CURUSER;

$wait = 0;
#Morgan: Remove wait times
/*
	if ($CURUSER["class"] < UC_VIP)
  {
	  $gigs = $CURUSER["uploaded"] / (1024*1024*1024);
	  $ratio = (($CURUSER["downloaded"] > 0) ? ($CURUSER["uploaded"] / $CURUSER["downloaded"]) : 0);
	  if ($ratio < 0.5 || $gigs < 5) $wait = 48;
	  elseif ($ratio < 0.65 || $gigs < 6.5) $wait = 24;
	  elseif ($ratio < 0.8 || $gigs < 8) $wait = 12;
	  elseif ($ratio < 0.95 || $gigs < 9.5) $wait = 6;
	  else $wait = 0;
  }
*/
?>
<table border="1" cellspacing="0" cellpadding="5">

<tr>

<td class="colhead" align="center">Category</td>
<td class="colhead" align="left">Name</td>
<!--<td class="heading" align="left">DL</td>-->
<?php
	if ($wait)
	{
		print("<td class=\"colhead\" align=\"center\">Wait</td>\n");
	}

	if ($variant == "mytorrents")
  {
  	print("<td class=\"colhead\" align=\"center\">Edit</td>\n");
    print("<td class=\"colhead\" align=\"center\">Visible</td>\n");
	}

?>
<td class="colhead" align="right">Files</td>
<td class="colhead" align="right">Comments</td>
<!--<td class="colhead" align="center">Rating</td>-->
<td class="colhead" align="center">Added</td>
<!--<td class="colhead" align="center">TTL</td>-->
<td class="colhead" align="center">Size</td>
<!--
<td class="colhead" align="right">Views</td>
<td class="colhead" align="right">Hits</td>
-->
<td class="colhead" align="center">Downloaded</td>
<td class="colhead" align="right">Uploaders</td>
<td class="colhead" align="right">Downloaders</td>
<td class="colhead" align="center">License</td>
<?php

    if ($variant == "index")
        print("<td class=\"colhead\" align='center'>Upped&nbsp;by</td>\n");

    print("</tr>\n");

    while ($row = mysql_fetch_assoc($res)) {
        $id = $row["id"];
        print("<tr>\n");

        print("<td align='center' style='padding: 0px'>");
        if (isset($row["cat_name"])) {
            print("<a href=\"browse.php?cat=" . $row["category"] . "\">");
            if (isset($row["cat_pic"]) && $row["cat_pic"] != "")
  #              print("<img border=\"0\" src=\"{$pic_base_url}caticons/{$row['cat_pic']}\" alt=\"{$row['cat_name']}\" />");
  		 print($row["cat_name"]);
            else
                print($row["cat_name"]);
            print("</a>");
        }
        else
            print("-");
        print("</td>\n");

        $dispname = htmlspecialchars($row["name"]);
        print("<td align='left'><a href=\"details.php?");
        if ($variant == "mytorrents")
            print("returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;");
        print("id=$id");
        if ($variant == "index")
            print("&amp;hit=1");
        print("\"><b>$dispname</b></a>\n");

				if ($wait)
				{
				  $elapsed = floor((time() - $row["added"]) / 3600);
	        if ($elapsed < $wait)
	        {
	          $color = dechex(floor(127*($wait - $elapsed)/48 + 128)*65536);
	          print("<td align='center'><span style=\"white-space: nowrap;\"><a href=\"faq.php#dl8\"><font color=\"$color\">" . number_format($wait - $elapsed) . " h</font></a></span></td>\n");
	        }
	        else
	          print("<td align='center'><span style=\"white-space: nowrap;\">None</span></td>\n");
        }

/*
        if ($row["nfoav"] && get_user_class() >= UC_POWER_USER)
          print("<a href='viewnfo.php?id=$row[id]''><img src=\"{$pic_base_url}viewnfo.gif" border='0' alt='View NFO' /></a>\n");
        if ($variant == "index")
            print("<a href=\"download.php/$id/" . rawurlencode($row["filename"]) . "\"><img src=\"{$pic_base_url}download.gif\" border='0' alt='Download' /></a>\n");

        else */ if ($variant == "mytorrents")
            print("</td><td align=\"center\"><a href=\"edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id=" . $row["id"] . "\">edit</a>\n");
print("</td>\n");
        if ($variant == "mytorrents") {
            print("<td align=\"right\">");
            if ($row["visible"] == "no")
                print("<b>no</b>");
            else
                print("yes");
            print("</td>\n");
        }

        if ($row["type"] == "single")
            print("<td align=\"right\">" . $row["numfiles"] . "</td>\n");
        else {
            if ($variant == "index")
                print("<td align=\"right\"><b><a href=\"filelist.php?id=$id\">" . $row["numfiles"] . "</a></b></td>\n");
            else
                print("<td align=\"right\"><b><a href=\"filelist.php?id=$id\">" . $row["numfiles"] . "</a></b></td>\n");
        }

        if (!$row["comments"])
            print("<td align=\"right\">" . $row["comments"] . "</td>\n");
        else {
            if ($variant == "index")
                print("<td align=\"right\"><b><a href=\"details.php?id=$id&amp;hit=1&amp;tocomm=1\">" . $row["comments"] . "</a></b></td>\n");
            else
                print("<td align=\"right\"><b><a href=\"details.php?id=$id&amp;page=0#startcomments\">" . $row["comments"] . "</a></b></td>\n");
        }

/*
        print("<td align=\"center\">");
        if (!isset($row["rating"]))
            print("---");
        else {
            $rating = round($row["rating"] * 2) / 2;
            $rating = ratingpic($row["rating"]);
            if (!isset($rating))
                print("---");
            else
                print($rating);
        }
        print("</td>\n");
*/
        print("<td align='center'><span style=\"white-space: nowrap;\">" . str_replace(",", "<br />", get_date( $row['added'],'')) . "</span></td>\n");
	#	$ttl = (28*24) - floor((time() - $row["added"]) / 3600);
	#	if ($ttl == 1) $ttl .= "<br />hour"; else $ttl .= "<br />hours";
        #print("<td align='center'>$ttl</td>\n");
        print("<td align='center'>" . str_replace(" ", "<br />", mksize($row["size"])) . "</td>\n");
//        print("<td align=\"right\">" . $row["views"] . "</td>\n");
//        print("<td align=\"right\">" . $row["hits"] . "</td>\n");
        $_s = "";
        if ($row["times_completed"] != 1)
          $_s = "s";
        print("<td align='center'>" . number_format($row["times_completed"]) . "<br />time$_s</td>\n");

        if ($row["seeders"]) {
            if ($variant == "index")
            {
               if ($row["leechers"]) $ratio = $row["seeders"] / $row["leechers"]; else $ratio = 1;
                print("<td align='right'><b><a href='peerlist.php?id=$id#seeders'><font color='" .
                  get_slr_color($ratio) . "'>" . $row["seeders"] . "</font></a></b></td>\n");
            }
            else
                print("<td align=\"right\"><b><a class=\"" . linkcolor($row["seeders"]) . "\" href=\"peerlist.php?id=$id#seeders\">" .
                  $row["seeders"] . "</a></b></td>\n");
        }
        else
            print("<td align=\"right\"><span class=\"" . linkcolor($row["seeders"]) . "\">" . $row["seeders"] . "</span></td>\n");

        if ($row["leechers"]) {
            if ($variant == "index")
                print("<td align='right'><b><a href='peerlist.php?id=$id#leechers'>" .
                   number_format($row["leechers"]) . "</a></b></td>\n");
            else
                print("<td align=\"right\"><b><a class=\"" . linkcolor($row["leechers"]) . "\" href=\"peerlist.php?id=$id#leechers\">" .
                  $row["leechers"] . "</a></b></td>\n");
        }
        else
            print("<td align=\"right\">0</td>\n");

	#license
	print("<td align='center'><a href=\"".$row["lic_url"]."\"target=\"_blank\">" .$row["lic_name"]."</a></td>");

        if ($variant == "index")
            print("<td align='center'>" . (isset($row["username"]) ? ("<a href='browse.php?user=" . $row["owner"] . "'><b>" . htmlspecialchars($row["username"]) . "</b></a>") : "<i>(unknown)</i>") . "</td>\n");

        print("</tr>\n");
    }

    print("</table>\n");

    //return $rows;
}

function commenttable($rows)
{
	global $CURUSER, $pic_base_url;
	begin_main_frame();
	begin_frame();
	$count = 0;
	foreach ($rows as $row)
	{
		print("<p class=sub>#" . $row["id"] . " by ");
    if (isset($row["username"]))
		{
			$title = $row["title"];
			if ($title == "")
				$title = get_user_class_name($row["class"]);
			else
				$title = htmlspecialchars($title);
        print("<a name='comm". $row["id"] .
        	"' href='userdetails.php?id=" . $row["user"] . "'><b>" .
        	htmlspecialchars($row["username"]) . "</b></a>" . ($row["donor"] == "yes" ? "<img src=\"{$pic_base_url}star.gif\" alt='Donor' />" : "") . ($row["warned"] == "yes" ? "<img src=".
    			"\"{$pic_base_url}warned.gif\" alt=\"Warned\" />" : "") . " ($title)\n");
		}
		else
   		print("<a name=\"comm" . $row["id"] . "\"><i>(orphaned)</i></a>\n");

		print(get_date( $row['added'],'') .
			($row["user"] == $CURUSER["id"] || get_user_class() >= UC_MODERATOR ? "- [<a href='comment.php?action=edit&amp;cid=$row[id]'>Edit</a>]" : "") .
			(get_user_class() >= UC_MODERATOR ? "- [<a href='comment.php?action=delete&amp;cid=$row[id]'>Delete</a>]" : "") .
			($row["editedby"] && get_user_class() >= UC_MODERATOR ? "- [<a href='comment.php?action=vieworiginal&amp;cid=$row[id]'>View original</a>]" : "") . "</p>\n");
		$avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($row["avatar"]) : "");
		if (!$avatar)
			$avatar = "{$pic_base_url}default_avatar.gif";
		$text = format_comment($row["text"]);
    if ($row["editedby"])
    	$text .= "<p><font size='1' class='small'>Last edited by <a href='userdetails.php?id={$row['editedby']}'><b>{$row['username']}</b></a> at ".get_date($row['editedat'],'DATE')."</font></p>\n";
		begin_table(true);
		print("<tr valign='top'>\n");
		print("<td align='center' width='150' style='padding: 0px'><img width='80' src=\"{$avatar}\" alt='' /></td>\n");
		print("<td class='text'>$text</td>\n");
		print("</tr>\n");
     end_table();
  }
	end_frame();
	end_main_frame();
}


?>