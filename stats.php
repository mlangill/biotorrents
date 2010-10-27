<?php
require "include/bittorrent.php";
require "include/user_functions.php";
require "include/html_functions.php";

dbconn(false);
loggedinorreturn();

if ($CURUSER['class'] < UC_MODERATOR)
	stderr("Error", "Permission denied.");

stdhead("Stats");

begin_main_frame();

$res = mysql_query("SELECT COUNT(*) FROM torrents") or sqlerr(__FILE__, __LINE__);
$n = mysql_fetch_row($res);
$n_tor = $n[0];

$res = mysql_query("SELECT COUNT(*) FROM peers") or sqlerr(__FILE__, __LINE__);
$n = mysql_fetch_row($res);
$n_peers = $n[0];

$uporder = isset($_GET['uporder']) ? $_GET['uporder'] : '';
$catorder = isset($_GET["catorder"]) ? $_GET["catorder"] : '';

if ($uporder == "lastul")
	$orderby = "last DESC, name";
elseif ($uporder == "torrents")
	$orderby = "n_t DESC, name";
elseif ($uporder == "peers")
	$orderby = "n_p DESC, name";
else
	$orderby = "name";

$query = "SELECT u.id, u.username AS name, MAX(t.added) AS last, COUNT(DISTINCT t.id) AS n_t, COUNT(p.id) as n_p
	FROM users as u LEFT JOIN torrents as t ON u.id = t.owner LEFT JOIN peers as p ON t.id = p.torrent WHERE u.class = 3
	GROUP BY u.id UNION SELECT u.id, u.username AS name, MAX(t.added) AS last, COUNT(DISTINCT t.id) AS n_t, COUNT(p.id) as n_p
	FROM users as u LEFT JOIN torrents as t ON u.id = t.owner LEFT JOIN peers as p ON t.id = p.torrent WHERE u.class > 3
	GROUP BY u.id ORDER BY $orderby";

$res = mysql_query($query) or sqlerr(__FILE__, __LINE__);

if (mysql_num_rows($res) == 0)
	stdmsg("Sorry...", "No uploaders.");
else
{
	begin_frame("Uploader Activity", True);
	begin_table();
	print("<tr>\n
	<td class='colhead'><a href=\"stats.php?uporder=uploader&amp;catorder=$catorder\" class='colheadlink'>Uploader</a></td>\n
	<td class='colhead'><a href=\"stats.php?uporder=lastul&amp;catorder=$catorder\" class='colheadlink'>Last Upload</a></td>\n
	<td class='colhead'><a href=\"stats.php?uporder=torrents&amp;catorder=$catorder\" class='colheadlink'>Torrents</a></td>\n
	<td class='colhead'>Perc.</td>\n
	<td class='colhead'><a href=\"stats.php?uporder=peers&amp;catorder=$catorder\" class='colheadlink'>Peers</a></td>\n
	<td class='colhead'>Perc.</td>\n
	</tr>\n");
	while ($uper = mysql_fetch_assoc($res))
	{
		print("<tr><td><a href='userdetails.php?id=".$uper['id']."'><b>".$uper['name']."</b></a></td>\n");
		print("<td " . ($uper['last']?(">".get_date( $uper['last'],'')." (".get_date( $uper['last'],'',0,1).")"):"align='center'>---") . "</td>\n");
		print("<td align='right'>" . $uper['n_t'] . "</td>\n");
		print("<td align='right'>" . ($n_tor > 0?number_format(100 * $uper['n_t']/$n_tor,1)."%":"---") . "</td>\n");
		print("<td align='right'>" . $uper['n_p']."</td>\n");
		print("<td align='right'>" . ($n_peers > 0?number_format(100 * $uper['n_p']/$n_peers,1)."%":"---") . "</td></tr>\n");
	}
	end_table();
	end_frame();
}

if ($n_tor == 0)
	stdmsg("Sorry...", "No categories defined!");
else
{
  if ($catorder == "lastul")
		$orderby = "last DESC, c.name";
	elseif ($catorder == "torrents")
		$orderby = "n_t DESC, c.name";
	elseif ($catorder == "peers")
		$orderby = "n_p DESC, name";
	else
		$orderby = "c.name";

  $res = mysql_query("SELECT c.name, MAX(t.added) AS last, COUNT(DISTINCT t.id) AS n_t, COUNT(p.id) AS n_p
	FROM categories as c LEFT JOIN torrents as t ON t.category = c.id LEFT JOIN peers as p
	ON t.id = p.torrent GROUP BY c.id ORDER BY $orderby") or sqlerr(__FILE__, __LINE__);

	begin_frame("Category Activity", True);
	begin_table();
	print("<tr><td class='colhead'><a href=\"stats.php?uporder=$uporder&amp;catorder=category\" class='colheadlink'>Category</a></td>
	<td class='colhead'><a href=\"stats.php?uporder=$uporder&amp;catorder=lastul\" class='colheadlink'>Last Upload</a></td>
	<td class='colhead'><a href=\"stats.php?uporder=$uporder&amp;catorder=torrents\" class='colheadlink'>Torrents</a></td>
	<td class='colhead'>Perc.</td>
	<td class='colhead'><a href=\"stats.php?uporder=$uporder&amp;catorder=peers\" class='colheadlink'>Peers</a></td>
	<td class='colhead'>Perc.</td></tr>\n");
	while ($cat = mysql_fetch_assoc($res))
	{
		print("<tr><td class='rowhead'>" . $cat['name'] . "</td>");
		print("<td " . ($cat['last']?(">".get_date( $cat['last'],'')." (".get_date( $cat['last'],'',0,1).")"):"align='center'>---") ."</td>");
		print("<td align='right'>" . $cat['n_t'] . "</td>");
		print("<td align='right'>" . number_format(100 * $cat['n_t']/$n_tor,1) . "%</td>");
		print("<td align='right'>" . $cat['n_p'] . "</td>");
		print("<td align='right'>" . ($n_peers > 0?number_format(100 * $cat['n_p']/$n_peers,1)."%":"---") . "</td></tr>\n");
	}
	end_table();
	end_frame();
}

end_main_frame();
stdfoot();
die;
?>