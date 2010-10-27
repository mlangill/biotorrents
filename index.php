<?php
ob_start("ob_gzhandler");

require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn(true);

loggedinorreturn();

/*
$a = @mysql_fetch_assoc(@mysql_query("SELECT id,username FROM users WHERE status='confirmed' ORDER BY id DESC LIMIT 1")) or die(mysql_error());
if ($CURUSER)
  $latestuser = "<a href='userdetails.php?id=" . $a["id"] . "'>" . $a["username"] . "</a>";
else
  $latestuser = $a['username'];
*/

$registered = number_format(get_row_count("users"));
$unverified = number_format(get_row_count("users", "WHERE status='pending'"));
$torrents = number_format(get_row_count("torrents"));
//$dead = number_format(get_row_count("torrents", "WHERE visible='no'"));

$r = mysql_query("SELECT value_u FROM avps WHERE arg='seeders'") or sqlerr(__FILE__, __LINE__);
$a = mysql_fetch_row($r);
$seeders = 0 + $a[0];
$r = mysql_query("SELECT value_u FROM avps WHERE arg='leechers'") or sqlerr(__FILE__, __LINE__);
$a = mysql_fetch_row($r);
$leechers = 0 + $a[0];
if ($leechers == 0)
  $ratio = 0;
else
  $ratio = round($seeders / $leechers * 100);
$peers = number_format($seeders + $leechers);
$seeders = number_format($seeders);
$leechers = number_format($leechers);


stdhead();
//echo "<font class='small''>Welcome to our newest member, <b>$latestuser</b>!</font>\n";

print("<table width='737' class='main' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>");
?>


<h2>BioTorrents</h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'>
<tr><td class='text'><ul>
<li>
BioTorrents allows scientists to rapidly share their results, datasets, and software using the popular <a href="http://en.wikipedia.org/wiki/BitTorrent_(protocol)">BitTorrent</a> file sharing technology.
</li>
<li>
All data is open-access and any illegal filesharing is not allowed on BioTorrents.
</li>
<li>
We encourage researchers and organizations to share their data on BioTorrents as an alternative to hosting files through FTP or HTTP for the following reasons:
<OL>
<LI>Using BioTorrents can allow researchers to download large datasets much faster.</LI>
<LI>BioTorrents can act as a central listing of results, datasets, and software that can be browsed and searched.</LI>
<LI>Data can be located on several servers allowing decentralization and availability of the data if one server becomes disabled</LI>
</OL> 
</li>
<LI>Please visit the <a href="browse.php">Browse</a> page to see a list of files available for download and the <a href="faq.php">FAQ</a> for more information.</LI>
</ul></tr></table>

<?php
print("<h2>Recent news");
if (get_user_class() >= UC_ADMINISTRATOR)
	print(" - <font class='small'>[<a class='altlink' href='news.php'><b>News page</b></a>]</font>");
print("</h2>\n");
$res = mysql_query("SELECT * FROM news
					WHERE added + ( 3600 *24 *45 ) >
					UNIX_TIMESTAMP( ) ORDER BY added DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) > 0)
{
	require_once "include/bbcode_functions.php";

	print("<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'>\n<ul>");
	while($array = mysql_fetch_assoc($res))
	{
	  print("<li>" . get_date( $array['added'],'DATE') . "<br />" . format_comment($array['body']));
    if (get_user_class() >= UC_ADMINISTRATOR)
    {
    	print(" <br /><font size=\"-2\">[<a class='altlink' href='news.php?action=edit&amp;newsid=" . $array['id'] . "&amp;returnto=index.php'><b>E</b></a>]</font>");
    	print(" <font size=\"-2\">[<a class='altlink' href='news.php?action=delete&amp;newsid=" . $array['id'] . "&amp;returnto=index.php'><b>D</b></a>]</font>");
    }
    print("</li>");
  }
  print("</ul></td></tr></table>\n");
}


?>


<h2>Stats</h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td align='center'>
<table class='main' border='1' cellspacing='0' cellpadding='5'>
<tr><td class='rowhead'>Registered users</td><td align='right'><?php echo $registered?></td></tr>
<!-- <tr><td class='rowhead'>Unconfirmed users</td><td align=right><?php echo $unverified?></td></tr> -->
<tr><td class='rowhead'>Torrents</td><td align='right'><?php echo $torrents?></td></tr>
<?php if (isset($peers)) { ?>
<tr><td class='rowhead'>Peers</td><td align='right'><?php echo $peers?></td></tr>
<tr><td class='rowhead'>Seeders</td><td align='right'><?php echo $seeders?></td></tr>
<tr><td class='rowhead'>Leechers</td><td align='right'><?php echo $leechers?></td></tr>
<tr><td class='rowhead'>Seeder/leecher ratio (%)</td><td align='right'><?=$ratio?></td></tr>
<?php } ?>
</table>
</td></tr></table>

<?php /*
<h2>Server load</h2>
<table width='100%' border='1' cellspacing='0' cellpadding='1'0><tr><td align=center>
<table class=main border='0' width=402><tr><td style='padding: 0px; background-image: url("<?php echo $pic_base_url?>loadbarbg.gif"); background-repeat: repeat-x'>
<?php $percent = min(100, round(exec('ps ax | grep -c apache') / 256 * 100));
if ($percent <= 70) $pic = "loadbargreen.gif";
elseif ($percent <= 90) $pic = "loadbaryellow.gif";
else $pic = "loadbarred.gif";
$width = $percent * 4;
print("<img height='1'5 width=$width src=\"{$pic_base_url}{$pic}\" alt='$percent%'>"); ?>
</td></tr></table>
</td></tr></table>
*/?>


<p><font class='small'>Disclaimer: None of the files shown here are actually hosted on this server. The links are provided solely by this site's users.
The administrator of this site (www.biotorrents.net) cannot be held responsible for what its users post, or any other actions of its users.
You may not use this site to distribute or download any material when you do not have the legal rights to do so.
It is your own responsibility to adhere to these terms.</font></p>

<!--
<p align='center'>
<a href="http://www.tbdev.net"><img src="<?=$pic_base_url?>tbdev_btn_red.png" border='0' alt="P2P Legal Defense Fund" /></a>
</p>

-->
</td></tr></table>

<?php

stdfoot();
?>
<!-- Site Meter -->
<script type="text/javascript" src="http://s27.sitemeter.com/js/counter.js?site=s27biotorrents">
</script>
<noscript>
<a href="http://s27.sitemeter.com/stats.asp?site=s27biotorrents" target="_top">
<img src="http://s27.sitemeter.com/meter.asp?site=s27biotorrents" alt="Site Meter" border="0"/></a>
</noscript>
<!-- Copyright (c)2009 Site Meter -->

