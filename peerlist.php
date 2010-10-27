<?php

require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once "include/bt_client_functions.php";
require_once "include/html_functions.php";

dbconn(false);

loggedinorreturn();

$id = (int)$_GET["id"];

if (!isset($id) || !is_valid_id($id))
	die();


function dltable($name, $arr, $torrent)
{

	global $CURUSER;
	
	if (!count($arr))
		return $s = "<div align='left'><b>No $name data available</b></div>\n";
	$s = "\n";
	$s .= "<table width='100%' class='main' border='1' cellspacing='0' cellpadding='5'>\n";
	$s .= "<tr><td colspan='11' class='colhead'>" . count($arr) . " $name</td></tr>" .
			"<tr><td class='colhead'>User/IP</td>" .
          "<td class='colhead' align='center'>Connectable</td>".
          "<td class='colhead' align='right'>Uploaded</td>".
          "<td class='colhead' align='right'>Rate</td>".
          "<td class='colhead' align='right'>Downloaded</td>" .
          "<td class='colhead' align='right'>Rate</td>" .
          "<td class='colhead' align='right'>Ratio</td>" .
          "<td class='colhead' align='right'>Complete</td>" .
          "<td class='colhead' align='right'>Connected</td>" .
          "<td class='colhead' align='right'>Idle</td>" .
          "<td class='colhead' align='left'>Client</td></tr>\n";
	$now = time();
	//$moderator = (isset($CURUSER) && get_user_class() >= UC_MODERATOR);
//$mod = get_user_class() >= UC_MODERATOR;
	foreach ($arr as $e) {


                // user/ip/port
                // check if anyone has this ip
                //($unr = mysql_query("SELECT username, privacy FROM users WHERE id=$e[userid] ORDER BY last_access DESC LIMIT 1")) or die;
                //$una = mysql_fetch_assoc($unr);
				if ($e["privacy"] == "strong") continue;
		$s .= "<tr>\n";
                if ($e["username"])
                  $s .= "<td><a href='userdetails.php?id=$e[userid]'><b>$e[username]</b></a></td>\n";
                else
                  $s .= "<td>" . ($mod ? $e["ip"] : preg_replace('/\.\d+$/', ".xxx", $e["ip"])) . "</td>\n";
		$secs = max(1, ($now - $e["st"]) - ($now - $e["la"]));
		//$revived = $e["revived"] == "yes";
        $s .= "<td align='center'>" . ($e['connectable'] == "yes" ? "Yes" : "<font color='red'>No</font>") . "</td>\n";
		$s .= "<td align='right'>" . mksize($e["uploaded"]) . "</td>\n";
		$s .= "<td align='right'><span style=\"white-space: nowrap;\">" . mksize(($e["uploaded"] - $e["uploadoffset"]) / $secs) . "/s</span></td>\n";
		$s .= "<td align='right'>" . mksize($e["downloaded"]) . "</td>\n";
		if ($e["seeder"] == "no")
			$s .= "<td align='right'><span style=\"white-space: nowrap;\">" . mksize(($e["downloaded"] - $e["downloadoffset"]) / $secs) . "/s</span></td>\n";
		else
			$s .= "<td align='right'><span style=\"white-space: nowrap;\">" . mksize(($e["downloaded"] - $e["downloadoffset"]) / max(1, $e["finishedat"] - $e['st'])) .	"/s</span></td>\n";
                if ($e["downloaded"])
				{
                  $ratio = floor(($e["uploaded"] / $e["downloaded"]) * 1000) / 1000;
                    $s .= "<td align=\"right\"><font color='" . get_ratio_color($ratio) . "'>" . number_format($ratio, 3) . "</font></td>\n";
				}
	               else
                  if ($e["uploaded"])
                    $s .= "<td align='right'>Inf.</td>\n";
                  else
                    $s .= "<td align='right'>---</td>\n";
		$s .= "<td align='right'>" . sprintf("%.2f%%", 100 * (1 - ($e["to_go"] / $torrent["size"]))) . "</td>\n";
		$s .= "<td align='right'>" . mkprettytime($now - $e["st"]) . "</td>\n";
		$s .= "<td align='right'>" . mkprettytime($now - $e["la"]) . "</td>\n";
		$s .= "<td align='left'>" . htmlspecialchars(getagent($e["agent"], $e['peer_id'])) . "</td>\n";
		$s .= "</tr>\n";
	}
	$s .= "</table>\n";
	return $s;
}

$res = mysql_query("SELECT * FROM torrents WHERE id = $id")
	or sqlerr();

if(mysql_num_rows($res) == 0)
	stderr('Error', 'Nothing to see here, move along!');
	
	$row = mysql_fetch_assoc($res);
	


			$downloaders = array();
			$seeders = array();
			//$subres = mysql_query("SELECT u.username, u.privacy, p.seeder, p.finishedat, p.downloadoffset, p.uploadoffset, p.ip, p.port, p.uploaded, p.downloaded, p.to_go, UNIX_TIMESTAMP( p.started ) AS st, p.connectable, p.agent, UNIX_TIMESTAMP( p.last_action ) AS la, p.userid, p.peer_id
			$subres = mysql_query("SELECT u.username, u.privacy, p.seeder, p.finishedat, p.downloadoffset, p.uploadoffset, p.ip, p.port, p.uploaded, p.downloaded, p.to_go, p.started AS st, p.connectable, p.agent, p.last_action AS la, p.userid, p.peer_id
			
FROM peers p
LEFT JOIN users u ON p.userid = u.id
WHERE p.torrent = $id") or sqlerr();
			
			if(mysql_num_rows($subres) == 0)
				stderr('Warning', 'No downloader/uploader data available!');
	
			while ($subrow = mysql_fetch_assoc($subres)) {
				if ($subrow["seeder"] == "yes")
					$seeders[] = $subrow;
				else
					$downloaders[] = $subrow;
			}

			function leech_sort($a,$b) {
                                if ( isset( $_GET["usort"] ) ) return seed_sort($a,$b);				
                                $x = $a["to_go"];
				$y = $b["to_go"];
				if ($x == $y)
					return 0;
				if ($x < $y)
					return -1;
				return 1;
			}
			function seed_sort($a,$b) {
				$x = $a["uploaded"];
				$y = $b["uploaded"];
				if ($x == $y)
					return 0;
				if ($x < $y)
					return 1;
				return -1;
			}

			usort($seeders, "seed_sort");
			usort($downloaders, "leech_sort");

stdhead('Details');

	print "<h1>Peerlist for <a href='$BASEURL/details.php?id=$id'>".htmlentities($row['name'])."</a></h1>";
	print dltable("Seeder(s)<a name='seeders'></a>", $seeders, $row);
	print '<br />' . dltable("Leecher(s)<a name='leechers'></a>", $downloaders, $row);
	
stdfoot();
?>