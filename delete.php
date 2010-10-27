<?php

require_once "include/bittorrent.php";
require_once "include/user_functions.php";


function bark($msg) {
  stdhead();
  stdmsg("Delete failed!", $msg);
  stdfoot();
  exit;
}

if (!mkglobal("id"))
	bark("missing form data");

$id = 0 + $id;
if (!is_valid_id($id))
	die();

dbconn();

loggedinorreturn();

function deletetorrent($id) {
    global $torrent_dir;
    mysql_query("DELETE FROM torrents WHERE id = $id");
    foreach(explode(".","peers.files.comments.ratings") as $x)
        mysql_query("DELETE FROM $x WHERE torrent = $id");
    unlink("$torrent_dir/$id.torrent");
}

$res = mysql_query("SELECT name,owner,seeders FROM torrents WHERE id = $id");
$row = mysql_fetch_assoc($res);
if (!$row)
	die();

if ($CURUSER["id"] != $row["owner"] && get_user_class() < UC_MODERATOR)
	bark("You're not the owner! How did that happen?\n");

$rt = 0 + $_POST["reasontype"];

if (!is_int($rt) || $rt < 1 || $rt > 5)
	bark("Invalid reason");

//$r = $_POST["r"]; // whats this
$reason = $_POST["reason"];

if ($rt == 1)
	$reasonstr = "Dead: 0 seeders, 0 leechers = 0 peers total";
elseif ($rt == 2)
	$reasonstr = "Dupe" . ($reason[0] ? (": " . trim($reason[0])) : "!");
elseif ($rt == 5)
{
	if (!$reason[1])
		bark("Please enter the reason for deleting this torrent.");
  $reasonstr = trim($reason[1]);
}

deletetorrent($id);

write_log("Torrent $id ($row[name]) was deleted by $CURUSER[username] ($reasonstr)\n");

stdhead("Torrent deleted!");

if (isset($_POST["returnto"]))
	$ret = "<a href=\"" . htmlspecialchars($_POST["returnto"]) . "\">Go back to whence you came</a>";
else
	$ret = "<a href=\"./\">Back to index</a>";

?>
<h2>Torrent deleted!</h2>
<p><?php echo $ret ?></p>
<?php

stdfoot();

?>