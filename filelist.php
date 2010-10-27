<?php

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/html_functions.php";

dbconn(false);

loggedinorreturn();

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if (!is_valid_id($id))
	die();

stdhead("Filelist");
		$s = "<a name='top'></a><table class='main' border=\"1\" cellspacing='0' cellpadding=\"5\">\n";

		$subres = mysql_query("SELECT * FROM files WHERE torrent = $id ORDER BY id");
		
		$s.="<tr><td class='colhead'>Path</td><td class='colhead' align='right'>Size</td></tr>\n";
		$counter = 0;
			while ($subrow = mysql_fetch_assoc($subres)) {
				
				if($counter !== 0 && $counter % 10 == 0)
					$s .= "<tr><td colspan='2' align='right'><a href='#top'><img src='$pic_base_url/top.gif' alt='' /></a></td></tr>";
				$s .= "<tr><td>" . htmlentities($subrow["filename"]) .
					"</td><td align=\"right\">" . htmlentities(mksize($subrow["size"])) . "</td></tr>\n";
				
				$counter++;
				}

				$s .= "</table>\n";
				
				print $s;


stdfoot();
?>