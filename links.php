<?php

require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn(false);
stdhead("Links");


function add_link($url, $title, $description = "")
{
  $text = "<a class='altlink' href=$url>$title</a>";
  if ($description)
    $text = "$text - $description";
  print("<li>$text</li>\n");
}

if ($CURUSER) { ?>

<?php } ?>
<table width='750' class='main' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>

<h2>BitTorrent Software</h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'><ul>
<li><a target=new class=altlink href=http://www.utorrent.com/>µTorrent**</a> - µTorrent is an efficient and feature rich BitTorrent client for <b>Windows & Macs</b>, sporting a very small footprint.</li>
<li><a target=new class=altlink href=http://deluge-torrent.org/>Deluge**</a> - Deluge is an open-source client for <b>Linux, Macs, and Windows</b> with many features and has a built-in web interface for easier remote access.</li>
<li><a target=new class=altlink href=http://www.transmissionbt.com/>Transmission</a> - Transmission runs on <b>Macs and Linux</b> and has add-ons that allow various ways to access the client remotely.</li>
<li><a target=new class=altlink href=http://ktorrent.org/>kTorrent</a> - kTorrent is a BitTorrent client for <b>Linux</b> that often comes bundled with KDE.</li>
<li><a target=new class=altlink href=http://www.vuze.com/>Vuze</a> - Vuze (old name was Azureus) is a java based client that runs on <b>Windows, Linux, and Mac</b> operating systems.</li>
<li><a target=new class=altlink href=http://libtorrent.rakshasa.no/>rTorrent</a> - rTorrent is a terminal-based client for <b>Linux</b>.</li>
<li><a target=new class=altlink href=http://libtorrent.rakshasa.no/>wTorrent</a> - wTorrent is web-interface for rTorrent.</li>
<li><a target=new class=altlink href=http://mktorrent.sourceforge.net/>mkTorrent**</a> - mkTorrent is a terminal-based program to create torrent files for uploading.</li>
</ul>
<b>**Recommended</b></td></tr></table>

<h2>Other pages on this site</h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'><ul>
<li><a class='altlink' href='rss.php'>RSS feed</a> -
  For use with RSS-enabled software. Provides links to the details page and directly to the torrent file for each newly uploaded torrent.</li>
<li><a class='altlink' href='rss_personalized.php'>Automatic downloading via RSS feed</a> - Using this link will allow some clients (uTorrent, Vuze, etc) to download and upload under your user account (instead of the guest account).</li>
<li><a class='altlink' href='get_upload_script.php'>Upload script</a> -
  a Perl script that creates and uploads a torrent directly to BioTorrents. Useful for those seeding from a remote server.</li>
</ul></td></tr></table>

<h2>BitTorrent Information</h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'><ul>
<li><a class='altlink' href='http://en.wikipedia.org/wiki/BitTorrent_(protocol)'>Wikipedia: BitTorrent_(Protocol)</a> - Everything you need to know about BitTorrent.</li>
<li><a class='altlink' href='http://torrentfreak.com/how-to-create-a-torrent/'>Creating and uploading</a> - Guide to creating and uploading your own torrents.</li>
<li><a class='altlink' href='http://www.tbdev.net/'>TBdev</a> - Forum community where you will find the code used on various torrent sites.</li>
</ul></td></tr></table>



<h2>Download sites</h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'><ul>
<li><a class='altlink' href='http://www.ncbi.nlm.nih.gov/Ftp/'>NCBI FTP Site</a> -
  Genomes, etc.</li>

</ul></td></tr></table>


<?php

stdfoot();

?>