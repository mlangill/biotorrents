<?php

require_once "include/bittorrent.php";
require_once "include/html_functions.php";
require_once "include/user_functions.php";
require_once "include/pager_functions.php";
require_once "include/torrenttable_functions.php";

dbconn(false);

loggedinorreturn();

stdhead($CURUSER["username"] . "'s torrents");

$where = "WHERE owner = " . $CURUSER["id"] . " AND banned != 'yes'";
$res = mysql_query("SELECT COUNT(*) FROM torrents $where");
$row = mysql_fetch_array($res,MYSQL_NUM);
$count = $row[0];

if (!$count) {
?>
<h1>No torrents</h1>
<p>You haven't uploaded any torrents yet, so there's nothing in this page.</p>
<?php
}
else {
	$pager = pager(20, $count, "mytorrents.php?");

	$res = mysql_query("SELECT torrents.type, torrents.comments, torrents.leechers, torrents.seeders, IF(torrents.numratings < $minvotes, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.id, categories.name AS cat_name, categories.image AS cat_pic, torrents.name, save_as, numfiles, added, size, views, visible, hits, times_completed, category FROM torrents LEFT JOIN categories ON torrents.category = categories.id $where ORDER BY id DESC ".$pager['limit']);

	print($pager['pagertop']);

	torrenttable($res, "mytorrents");

	print($pager['pagerbottom']);
}

stdfoot();

?>