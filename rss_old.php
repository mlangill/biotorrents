<?php
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
dbconn(); 

#loggedinorreturn();

/* RSS feeds */

if (($fd1 = @fopen("rss.xml", "w")))
{
        $cats = "";
        $res = mysql_query("SELECT id, name FROM categories");
        while ($arr = mysql_fetch_assoc($res)){
                $cats[$arr["id"]] = $arr["name"];
	}

 	$s = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n<channel>\n" .
                "<title>$SITENAME</title>\n<description>BioTorrents</description>\n<link>$BASEURL/</link>\n<atom:link href=\"$BASEURL/rss.xml\" rel=\"self\" type=\"application/rss+xml\"/>\n";
        @fwrite($fd1, $s);
        $r = mysql_query("SELECT torrents.id,torrents.name,torrents.descr,torrents.filename,torrents.category,torrents.size,torrents.version,licenses.name AS lic_name FROM torrents LEFT JOIN licenses ON torrents.license=licenses.id ORDER BY added DESC LIMIT 20") or sqlerr(__FILE__, __LINE__);
        while ($a = mysql_fetch_assoc($r))
        {
                $cat = $cats[$a["category"]];
                $s = "<item>\n<title>" . htmlspecialchars($a["name"] . " ($cat)- ".$a["lic_name"]) . "</title>\n" .
                        "<category>$cat</category><version>".$a["version"]."</version><license>".$a["lic_name"]."</license><description>" . $a["descr"] . "</description>\n";
                @fwrite($fd1, $s);
                @fwrite($fd1, "<guid>$BASEURL/details.php?id=$a[id]&amp;hit=1</guid>\n");
                $filename = htmlspecialchars($a["filename"]);
                @fwrite($fd1, "<enclosure url=\"$BASEURL/download.php/$a[id]/$filename\" length=\"$a[size]\" type=\"application/x-bittorrent\"/>\n</item>\n");
        }
        $s = "</channel>\n</rss>\n";
        @fwrite($fd1, $s);
        @fclose($fd1);
}
?>