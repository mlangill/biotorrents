<?php

ob_start("ob_gzhandler");

require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once "include/bbcode_functions.php";
require_once "include/pager_functions.php";
require_once "include/torrenttable_functions.php";
require_once "include/html_functions.php";


function ratingpic($num) {
    global $pic_base_url;
    $r = round($num * 2) / 2;
    if ($r < 1 || $r > 5)
        return;
    return "<img src=\"{$pic_base_url}{$r}.gif\" border=\"0\" alt=\"rating: $num / 5\" />";
}


dbconn(false);

loggedinorreturn();

$id = 0 + $_GET["id"];

if (!isset($id) || !is_valid_id($id))
	die();
	
	
	if (isset($_GET["hit"])) {
		mysql_query("UPDATE torrents SET views = views + 1 WHERE id = $id");
		if ($_GET["tocomm"])
			header("Location: $BASEURL/details.php?id=$id&page=0#startcomments");
		elseif ($_GET["filelist"])
			header("Location: $BASEURL/details.php?id=$id&filelist=1#filelist");
		elseif ($_GET["toseeders"])
			header("Location: $BASEURL/peerlist.php?id=$id#seeders");
		elseif ($_GET["todlers"])
			header("Location: $BASEURL/peerlist.php?id=$id#leechers");
		else
			header("Location: $BASEURL/details.php?id=$id");
		exit();
	}
	
$res = mysql_query("SELECT torrents.seeders, torrents.banned, torrents.leechers, torrents.info_hash, torrents.filename, LENGTH(torrents.nfo) AS nfosz, torrents.last_action AS lastseed, torrents.numratings, torrents.name, IF(torrents.numratings < $minvotes, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.owner, torrents.save_as, torrents.descr, torrents.visible, torrents.size, torrents.added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.numfiles, categories.name AS cat_name, users.username, licenses.name AS lic_name, torrents.version FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN users ON torrents.owner = users.id LEFT JOIN licenses ON torrents.license = licenses.id WHERE torrents.id = $id")
	or sqlerr();
$row = mysql_fetch_assoc($res);

$owned = $moderator = 0;
	if (get_user_class() >= UC_MODERATOR)
		$owned = $moderator = 1;
	elseif ($CURUSER["id"] == $row["owner"])
		$owned = 1;
//}

if (!$row || ($row["banned"] == "yes" && !$moderator))
	stderr("Error", "No torrent with ID.");




		stdhead("Details for \"" . htmlentities($row["name"], ENT_QUOTES) . "\"");

		if ($CURUSER["id"] == $row["owner"] || get_user_class() >= UC_MODERATOR)
			$owned = 1;
		else
			$owned = 0;

		$spacer = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

		if (isset($_GET["uploaded"])) {
			print("<h2>Successfully uploaded!</h2>\n");
			print("<p>You can start seeding now. <b>Note</b> that the torrent won't be visible until you do that!</p>\n");
		}
		elseif (isset($_GET["edited"])) {
			print("<h2>Successfully edited!</h2>\n");
			if (isset($_GET["returnto"]))
				print("<p><b>Go back to <a href=\"" . htmlspecialchars($_GET["returnto"]) . "\">whence you came</a>.</b></p>\n");
		}
		/* elseif (isset($_GET["searched"])) {
			print("<h2>Your search for \"" . htmlspecialchars($_GET["searched"]) . "\" gave a single result:</h2>\n");
		} */
		elseif (isset($_GET["rated"]))
			print("<h2>Rating added!</h2>\n");

    $s = htmlentities( $row["name"], ENT_QUOTES );
		print("<h1>$s</h1>\n");
		print('<!-- AddThis Button BEGIN -->
<div class="addthis_toolbox addthis_default_style" style="width:160px; margin:0 auto">
<a href="http://www.addthis.com/bookmark.php?v=250&amp;username=xa-4be3117d6bfa1709" class="addthis_button_compact">Share</a>
<span class="addthis_separator">|</span>
<a class="addthis_button_facebook"></a>
<a class="addthis_button_myspace"></a>
<a class="addthis_button_google"></a>
<a class="addthis_button_twitter"></a>
</div>
<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=xa-4be3117d6bfa1709"></script>
<!-- AddThis Button END -->');
		print('<p>First time downloading? See <a href="faq.php#dl0">FAQ:Download</a> for help.</p>');
                print("<table width='750' border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");

		$url = "edit.php?id=" . $row["id"];
		if (isset($_GET["returnto"])) {
			$addthis = "&amp;returnto=" . urlencode($_GET["returnto"]);
			$url .= $addthis;
			$keepget .= $addthis;
		}
		$editlink = "a href=\"$url\" class=\"sublink\"";

//		$s = "<b>" . htmlspecialchars($row["name"]) . "</b>";
//		if ($owned)
//			$s .= " $spacer<$editlink>[Edit torrent]</a>";
//		tr("Name", $s, 1);

        $download_href = "download.php/$id/" . rawurlencode($row["filename"]);

		print('<tr><td class="rowhead_big" width="1%">Download</td><td width="99%"><a class="biglink" href="'
		.$download_href
		.'">'
		. htmlspecialchars($row["filename"])
		. "</a>"
		. '<a href="http://www.bitlet.org?torrent='
		. "$BASEURL/$download_href"
		. '" style="color: #666; font-weight:bold; border: 1px solid #09f; background-color: #fec" >BitLet<span style="color:#09f">.org</span></a>'
		."</td></tr>");

//		tr("Downloads&nbsp;as", $row["save_as"]);

		function hex_esc($matches) {
			return sprintf("%02x", ord($matches[0]));
		}
		tr("Info hash", preg_replace_callback('/./s', "hex_esc", hash_pad($row["info_hash"])));

		#Morgan: Add version details
		$version = $row["version"];
		if (isset($row["version"]) && $row["version"]){
		   $ver_res = mysql_query("SELECT id,name FROM torrents WHERE version= $version AND id !=$id ORDER BY added DESC");
		   $ver_list ="";
		   while ($ver_row = mysql_fetch_array($ver_res)){
        	   	$ver_list .= "<a href=details.php?id=".$ver_row["id"].">" . $ver_row["name"]. "</a><br/>";		   
		   }
		   if($ver_list == ""){
		   		$ver_list="(None Selected)";
		   }
		   $ver_list .= "<a href=\"rss.php?ver=".$version."\" ><img align=center border=0 src=\"pic/rss_small.png\" alt=\"RSS\" /></a>";
		   tr("Alternative Versions", $ver_list, 1);
		}else{
			tr("Alternative Versions", "(None Selected)");
		}


		if (!empty($row["descr"]))
			print("<tr><td class='rowhead'>Description</td><td><div style='background-color:#d9e2ff;width:100%;height:200px;overflow: auto'>". str_replace(array("\n", "  "), array("\n", "&nbsp; "), format_comment( $row["descr"] ))."</div></td></tr>");
			
if (get_user_class() >= UC_POWER_USER && $row["nfosz"] > 0)
  print("<tr><td class='rowhead'>NFO</td><td align='left'><a href='viewnfo.php?id=$row[id]'><b>View NFO</b></a> (" .
     mksize($row["nfosz"]) . ")</td></tr>\n");
		if ($row["visible"] == "no")
			tr("Visible", "<b>no</b> (dead)", 1);
		//if ($moderator)
			//tr("Banned", $row["banned"]);

		if (isset($row["cat_name"]))
			tr("Category", $row["cat_name"]);
		else
			tr("Category", "(None Selected)");

		if (isset($row["lic_name"]))
			tr("License", $row["lic_name"]);
		else
			tr("License", "(None Selected)");

		tr("Last&nbsp;seeder", "Last activity " .get_date( $row['lastseed'],'',0,1));
		tr("Size",mksize($row["size"]) . " (" . number_format($row["size"]) . " bytes)");
/*
		$s = "";
		$s .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td valign=\"top\" class=embedded>";
		if (!isset($row["rating"])) {
			if ($minvotes > 1) {
				$s .= "none yet (needs at least $minvotes votes and has got ";
				if ($row["numratings"])
					$s .= "only " . $row["numratings"];
				else
					$s .= "none";
				$s .= ")";
			}
			else
				$s .= "No votes yet";
		}
		else {
			$rpic = ratingpic($row["rating"]);
			if (!isset($rpic))
				$s .= "invalid?";
			else
				$s .= "$rpic (" . $row["rating"] . " out of 5 with " . $row["numratings"] . " vote(s) total)";
		}
		$s .= "\n";
		$s .= "</td><td class='embedded'>$spacer</td><td valign=\"top\" class='embedded'>";
	//	if (!isset($CURUSER))
	//		$s .= "(<a href=\"login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;nowarn=1\">Log in</a> to rate it)";
	//	else {
			$ratings = array(
					5 => "Kewl!",
					4 => "Pretty good",
					3 => "Decent",
					2 => "Pretty bad",
					1 => "Sucks!",
	//   	);
			if (!$owned || $moderator) {
				if (!empty($row['numratings'])){
$xres = mysql_query("SELECT rating, added FROM ratings WHERE torrent = $id AND user = " . $CURUSER["id"]);
$xrow = mysql_fetch_assoc($xres);
}
if (!empty($xrow))
					$s .= "(you rated this torrent as \"" . $xrow["rating"] . " - " . $ratings[$xrow["rating"]] . "\")";
				else {
					$s .= "<form method=\"post\" action=\"takerate.php\"><input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
					$s .= "<select name=\"rating\">\n";
					$s .= "<option value=\"0\">(add rating)</option>\n";
					foreach ($ratings as $k => $v) {
						$s .= "<option value=\"$k\">$k - $v</option>\n";
					}
					$s .= "</select>\n";
					$s .= "<input type=\"submit\" value=\"Vote!\" />";
					$s .= "</form>\n";
				}
			}
		}
		$s .= "</td></tr></table>";
		tr("Rating", $s, 1);

*/

		tr("Added", get_date( $row['added'],'LONG'));
		tr("Views", $row["views"]);
		tr("Hits", $row["hits"]);
		tr("Downloaded", $row["times_completed"] . " time(s)");

		$keepget = "";
		$uprow = (isset($row["username"]) ? ("<a href='userdetails.php?id=" . $row["owner"] . "'><b>" . htmlspecialchars($row["username"]) . "</b></a>") : "<i>unknown</i>");
		if ($owned)
			$uprow .= " $spacer<$editlink><b>[Edit this torrent]</b></a>";
		tr("Upped by", $uprow, 1);

		if ($row["type"] == "multi") {
			if (!isset($_GET["filelist"]))
				tr("Num files<br /><a href=\"filelist.php?id=$id\" class=\"sublink\">[See full list]</a>", $row["numfiles"] . " files", 1);
			else {
				tr("Num files", $row["numfiles"] . " files", 1);

				
			}
		}

		tr("Peers<br /><a href=\"peerlist.php?id=$id#seeders\" class=\"sublink\">[See full list]</a>", $row["seeders"] . " seeder(s), " . $row["leechers"] . " leecher(s) = " . ($row["seeders"] + $row["leechers"]) . " peer(s) total", 1);
		print "</table>";

		//stdhead("Comments for torrent \"" . $row["name"] . "\"");
		print("<h1>Comments for <a href='details.php?id=$id'>" . htmlentities( $row["name"], ENT_QUOTES ) . "</a></h1>\n");


	print("<p><a name=\"startcomments\"></a></p>\n");

	$commentbar = "<p align='center'><a class='index' href='comment.php?action=add&amp;tid=$id'>Add a comment</a></p>\n";

	$subres = mysql_query("SELECT COUNT(*) FROM comments WHERE torrent = $id");
	$subrow = mysql_fetch_array($subres,MYSQL_NUM);
	$count = $subrow[0];

	if (!$count) {
		print("<h2>No comments yet</h2>\n");
	}
	else {
		$pager = pager(20, $count, "details.php?id=$id&amp;", array('lastpagedefault' => 1));

		$subres = mysql_query("SELECT comments.id, text, user, comments.added, editedby, editedat, avatar, warned, ".
                  "username, title, class, donor FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = " .
                  "$id ORDER BY comments.id ".$pager['limit']) or sqlerr(__FILE__, __LINE__);
		$allrows = array();
		while ($subrow = mysql_fetch_assoc($subres))
			$allrows[] = $subrow;

		print($commentbar);
		print($pager['pagertop']);

		commenttable($allrows);

		print($pager['pagerbottom']);
	}

	print($commentbar);


stdfoot();

?>
