<?php
require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn();
loggedinorreturn();
if ($CURUSER['class'] < UC_MODERATOR) stderr("Error", "Permission denied");

if ($_SERVER["REQUEST_METHOD"] == "POST")
	$ip = isset($_POST["ip"]) ? $_POST["ip"] : false;
else
	$ip = isset($_GET["ip"]) ? $_GET["ip"] : false;
if ($ip)
{
	$nip = ip2long($ip);
	if ($nip == -1)
	  stderr("Error", "Bad IP.");
	$res = mysql_query("SELECT * FROM bans WHERE $nip >= first AND $nip <= last") or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($res) == 0)
	  stderr("Result", "The IP address <b>".htmlentities($ip, ENT_QUOTES)."</b> is not banned.");
	else
	{
	  $banstable = "<table class='main' border='0' cellspacing='0' cellpadding='5'>\n" .
	    "<tr><td class='colhead'>First</td><td class='colhead'>Last</td><td class='colhead'>Comment</td></tr>\n";
	  while ($arr = mysql_fetch_assoc($res))
	  {
	    $first = long2ip($arr["first"]);
	    $last = long2ip($arr["last"]);
	    $comment = htmlspecialchars($arr["comment"]);
	    $banstable .= "<tr><td>$first</td><td>$last</td><td>$comment</td></tr>\n";
	  }
	  $banstable .= "</table>\n";
	  stderr("Result", "<table border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded' style='padding-right: 5px'><img src=\"{$pic_base_url}smilies/excl.gif\" alt='' /></td><td class='embedded'>The IP address <b>$ip</b> is banned:</td></tr></table><p>$banstable</p>");
	}
}
stdhead();

?>
<h1>Test IP address</h1>
<form method='post' action='testip.php'>
<table border='1' cellspacing='0' cellpadding='5'>
<tr><td class='rowhead'>IP address</td><td><input type='text' name='ip' /></td></tr>
<tr><td colspan='2' align='center'><input type='submit' class='btn' value='OK' /></td></tr>
</table>
</form>

<?php
stdfoot();
?>