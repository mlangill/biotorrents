<?php
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once "include/search.php";
dbconn(); 

loggedinorreturn();

/* RSS feeds */


$s = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n<channel>\n" .
                "<title>$SITENAME</title>\n<description>BioTorrents</description>\n<link>$BASEURL/</link>\n<atom:link href=\"$BASEURL/rss.php\" rel=\"self\" type=\"application/rss+xml\"/>\n";
print $s;

list($res,$wherecatina,$pager) = search($_GET, $CURUSER);
        while ($a = mysql_fetch_assoc($res))
        {
                $cat = $a["cat_name"];
                //$s = "<item>\n<title>" . htmlspecialchars($a["name"] . " ($cat)- ".$a["lic_name"]) . "</title>\n" . "<category>$cat</category><version>".$a["version"]."</version><license>".$a["lic_name"]."</license><description>" . htmlentities($a["descr"]) . "</description>\n";
                $s = "<item>\n<title>" . htmlspecialchars($a["name"] . " ($cat)- ".$a["lic_name"]) . "</title>\n" . "<category>$cat</category><description>" . htmlentities($a["descr"]) . "</description>\n";
		print $s;
		$link ="$BASEURL/details.php?id=$a[id]&amp;hit=1";
		print "<link>$link</link>\n";
		$date=date('r',$a['added']);
		print "<pubDate>$date</pubDate>\n";
                print "<guid>$link</guid>\n";
                $filename = urlencode($a["filename"]);
                print "<enclosure url=\"$BASEURL/download.php/$a[id]/$filename\" length=\"$a[size]\" type=\"application/x-bittorrent\"/>\n</item>\n";
        }
        $s = "</channel>\n</rss>\n";
        print $s;

?>