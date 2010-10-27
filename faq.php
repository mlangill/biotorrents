<?php

require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn(false);
stdhead("FAQ");
?>

<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'>
<b>Welcome to BioTorrents!</b><br />
<br />
The goal of BioTorrents is to allow easier and faster exchange of science-related <b>open-access</b> software and datasets.
<br/>
<br/>
Illegal material is not welcome on this website at any time and if found should be reported immediately to the <a class='altlink' href='staff.php'>staff</a>. 
<br/>
<br/>
Downloading does not require an account with us, but we would encourage people to <a href='signup.php' class='altlink'>sign up</a> so they can upload their own material and interact with more parts of the website. 
<br />
</td></tr></table>
</td></tr></table>
<br />
<br />
<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<h2>Contents</h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'>
<a href="#site"><b>Site Information</b></a>
  <ul>
    <li><a href="#site1" class="altlink">Who created BioTorrents?</a></li>
     <li><a href="#site2" class="altlink">Who is hosting BioTorrents?</a></li>
    <li><a href="#site2.1" class="altlink">Is there a BioTorrents mailing list?</a></li>
    <li><a href="#site3" class="altlink">How can I help with BioTorrents?</a></li>
    <li><a href="#site4" class="altlink">Where can I get a copy of the source code?</a></li> 
    <li><a href="#site5" class="altlink">Is there a manuscript I should reference if I use BioTorrents?</a></li> 
  </ul>
</li>

<a href="#dl"><b>Downloading</b></a>
  <ul>
    	<li><a href="#dl0" class="altlink">How do I download the files?</a></li>
	<li><a href="#dl9" class="altlink">How can I improve my download speed?</a></li>
    	<li><a href="#dl4" class="altlink">How do I resume a broken download or reseed something?</a></li>
    	<li><a href="#dl5" class="altlink">Why do my downloads sometimes stall at 99%?</a></li>
    	<li><a href="#dl6" class="altlink">What are these &quot;a piece has failed an hash check&quot; messages?</a></li>
    	<li><a href="#dl7" class="altlink">The torrent is supposed to be 100MB. How come I downloaded 120MB?</a></li>
  </ul>
<br />
<a href="#up"><b>Uploading</b></a>
  <ul>
    	<li><a href="#up1" class="altlink">Why can't I upload torrents?</a> </li>
	<li><a href="#up2" class="altlink">What do I need to be able to upload?</a></li>
	<li><a href="#up3" class="altlink">What is the tracker's announce url?</a></li>
	<li><a href="#up4" class="altlink">I plan on uploading many torrents from a server. Can I avoid having to visit the upload page for each one?</a></li>
	<li><a href="#up5" class="altlink">Can I password protect my files and still use BioTorrents?</a></li>
	<li><a href="#up6" class="altlink">I would like to include a web link directly to my data on BioTorrents within my manuscript, but I don't want to release the data until the paper has been accepted. How can I do this?</a></li>
  </ul>
<br />
<a href="#user"><b>User information</b></a>
  <ul>
	  <li><a href="#user1" class="altlink">I registered an account but did not receive the confirmation e-mail!</a></li>
	  <li><a href="#user2" class="altlink">I've lost my user name or password! Can you send it to me?</a></li>
	  <li><a href="#user3" class="altlink">Can you rename my account?</a></li>
	  <li><a href="#user4" class="altlink">Can you delete my (confirmed) account?</a>
	  <li><a href="#userb" class="altlink">So, what's MY ratio?</a></li>
	  <li><a href="#user5" class="altlink">Why is my IP displayed on my details page?</a></li>
	  <li><a href="#user7" class="altlink">My IP address is dynamic. How do I stay logged in?</a>
	  <li><a href="#user8" class="altlink">Why am I listed as not connectable? (And why should I care?)</a></li>
	  <li><a href="#userd" class="altlink">How do I add an avatar to my profile?</a></li>
  </ul>
<br />

<br />
<br />
<a href="#other"><b>What if I can't find the answer to my problem here?</b></a>
</td></tr></table>
</td></tr></table>
<br />
<br />
<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<h2>Site Information<a name="dl" id="dl"></a></h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'>
<br />
<b>Who created BioTorrents?</b><a name="site1" id="site1"></a></b>
<br />
BioTorrents was created by <a class='altlink' href="http://morganlangille.com">Morgan Langille</a>.
<br />
<br />
<b>Who is hosting BioTorrents?</b><a name="site2" id="site2"></a></b>
<br />
BioTorrents is hosted by <a class='altlink' href="http://bobcat.genomecenter.ucdavis.edu/mediawiki/index.php/Main_Page">Dr. Jonathan Eisen's lab</a> at the <a class='altlink' href="http://genomics.ucdavis.edu/">UC Davis genome center</a>.
<br/>
<br/>
<b>Is there a BioTorrents mailing list?</b><a name="site2.1" id="site2.1"></a></b>
<br />
Yes. You can join the <a class='altlink' href="http://groups.google.com/group/biotorrents">BioTorrents Google Group</a>.
<br/>
<br/>
<b>How can I help with BioTorrents?</b><a name="site3" id="site3"></a></b>
<br/>
Please <a class='altlink' href="sendmessage.php?receiver=1">contact us</a> if you want to help in anyway. 
<br/>
<br/>
<b>Where can I get a copy of the source code?</b><a name="site4" id="site4"></a></b>
<br/>
BioTorrents was derived from the <a class='altlink' href='http://www.tbdev.net/'>TBDev.net</a> source code. The BioTorrents.net source code is available under the GNU GPL on the <a class='altlink' href="browse.php">browse page</a>.   
<br/>
<br/>
<b>Is there a manuscript I should reference if I use BioTorrents?</b><a name="site5" id="site5"></a></b>
<br/>
Please cite our manuscript:  <a class='altlink' href="http://www.plosone.org/article/info%3Adoi%2F10.1371%2Fjournal.pone.0010071">Langille MGI,  Eisen JA, 2010 BioTorrents: A File Sharing Service for Scientific Data. PLoS ONE 5(4): e10071. </a> 
<br/>
</td></tr></table>
<br/>
<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<h2>Downloading<a name="dl" id="dl"></a></h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'>
<br />
<b>How do I download the files?</b><a name="dl0" id="dl0"></a><br />
<br />
<UL>
<LI>Download and install any BitTorrent program (see list <a class="altlink" href="links.php">here</a>).</LI>
<LI><a class="altlink" href="browse.php">Browse/Search</a> through the available files on BioTorrents and download the associated torrent file.</LI>
<LI>Open the file on your computer with your installed BitTorrent client and wait for the file to finish downloading.</LI>
</UL>
<br />
<b>How can I improve my download speed?<a name="dl9"></a></b>
<br/>
The download speed mostly depends on the number of seeders for the torrent you are downloading. If the number of seeders is much higher to the number of leechers (downloaders) then the download speeds will be faster. Also, sometimes torrents speeds will be slow at the beginning, but as new peers are found the speeds can increase dramatically. Please remember to help seed the torrent by leaving your BitTorrent client on after it is done downloading.
<br />
<br />
Also, to improve your speed <b>make sure that you are connectable.</b> <a name="dlsp2"></a>
See the <i><a href="#user8" class="altlink">Why am I listed as not connectable?</a></i> &nbsp;section.<br />
<br/>
<br/>
<b>How do I resume a broken download or reseed something?</b><a name="dl4" id="dl4"></a><br />
<br />
Open the .torrent file. When your client asks you for a location, choose the location of
the existing file(s) and it will resume/reseed the torrent.<br />
<br />
<br />
<b>Why do my downloads sometimes stall at 99%?</b><a name="dl5"></a><br />
<br />
The more pieces you have, the harder it becomes to find peers who have pieces you are missing.
That is why downloads sometimes slow down or even stall when there are just a few percent remaining.
Just be patient and you will, sooner or later, get the remaining pieces.<br />
<br />
<br />
<b>What are these &quot;a piece has failed an hash check&quot; messages?</b><a name="dl6"></a>
<br />
<br />
Bittorrent clients check the data they receive for integrity. When a piece fails this check it is
automatically re-downloaded. Occasional hash fails are a common occurrence, and you shouldn't worry.<br />
<br />
<br />
<b>The torrent is supposed to be 100MB. How come I downloaded 120MB?</b><a name="dl7"></a>
<br />
<br />
If your client receives bad data it will have to redownload it, therefore
the total downloaded may be larger than the torrent size.
<br />
<br />
</td></tr></table>
</td></tr></table>
<br />
<br />
<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<h2>Uploading<a name="up"></a></h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'>
<br />
<b>Why can't I upload torrents?</b><a name="up1"></a><br />
<br />
Only users that have <a href="signup.php" class='altlink'>signed up</a> for an account have permission to upload torrents.<br />
<br />
<b>What do I need to be able to upload?</b><a name="up2"></a><br />
<br />
Files can be shared on BioTorrents using three easy steps:
<ol>
<LI>Use any <a href="links.php" class="altlink">BitTorrent program</a> to create a torrent file. See this <a class="altlink" href="http://torrentfreak.com/how-to-create-a-torrent/" class="altlink">guide</a> for more information</LI>
<LI>Visit the <a href="upload.php" class="altlink">upload page</a> and fill in details about the file you are sharing.</LI>
<LI>Leave your computer and BitTorrent client running (seeding) to allow other users to download the files from you. </LI>
</OL>
<UL>
<LI>Note 1: Uploaded torrents that are not being seeded will not appear on the homepage, but are viewable by including <a href="browse.php?incldead=1" class="altlink">"dead" torrents</a> on the browse page.</LI>
<LI>Note 2: BitTorrent clients often require a port to be open to properly share files (see <a class="altlink" href="#user8">not connectable</a>).</LI>
</UL>
<br/>
<b>What is the tracker's announce url?</b><a name="up3"></a><br />
<br />
The tracker's announce url is needed when creating torrents and it is shown on the  <a href="upload.php" class='altlink'>upload page</a>.<br />
<br />
<b>I plan on uploading many torrents from a server. Can I avoid having to visit the upload page for each one?</b><a name="up4"></a><br />
<br />
Yes!! The following Perl script will create the torrent file and upload it to BioTorrents under your username:<a href="get_upload_script.php" class='altlink'>upload_to_biotorrents_as_xxxx.pl</a>.This script also requires that <a href="http://mktorrent.sourceforge.net/" class="altlink">mkTorrent</a> be installed for creation of the torrent files.<br />
<br />
<b>Can I password protect my files and still use BioTorrents?</b><a name="up5"></a><br/>
<br/>
Those users that are not ready to share their data with the public can still use BioTorrents to transfer their files. It is recommended that users password protect their data before creation of their torrent and that users upload these torrents to the <a href='browse.php?cat=8'>"Password Protected"</a> category. More discussion is <a href='forums.php?action=viewtopic&topicid=1'>here</a>.
<br/>
<br/>
<b>I would like to include a web link directly to my data on BioTorrents within my manuscript, but I don't want to release the data until the paper has been accepted. How can I do this?</b><a name="up6"></a><br/>
<br/>
Create and upload the torrent as normal, but do not start seeding it (i.e. turn off your torrent client). You can use the URL from your newly uploaded torrent in your manuscript, but it will not be downloadable or visible on the main "active" browse page until you start seeding it (remember to turn on your client once the paper is accepted). Torrents that are not being seeded are visible on the <a href="browse.php?incldead=1" class="altlink">"dead" browse page</a> and any torrents that remain dead/inactive for 1 year are removed.
<br/>
<br/>
</td></tr></table>
</td></tr></table>
<br />
<br />
<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<h2>User information<a name="user" id="user"></a></h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'>
<b>I registered an account but did not receive the confirmation e-mail!</b><a name="user1" id="user1"></a><br />
<br />
Please check your spam filter/folder to make sure it was not redirected there by mistake. Otherwise, you can use <a class='altlink' href='delacct.php'>this form</a> to delete the account so you can re-register.
Please send us a <a href='sendmessage.php?receiver=1' class='altlink'>message</a> with your email if you continue to have problems. 
<br />
<br />
<br />
<b>I've lost my user name or password! Can you send it to me?</b><a name="user2" id="user2"></a><br />
<br />
Please use <a class='altlink' href='recover.php'>this form</a> to have the login details mailed back to you.<br />
<br />
<br />
<b>Can you rename my account?</b><a name="user3" id="user3"></a><br />
<br />
We do not rename accounts. Please create a new one. (Use <a href='delacct.php' class='altlink'>this form</a> to
delete your present account.)<br />
<br />
<br />
<b>Can you delete my (confirmed) account?</b><a name="user4" id="user4"></a><br />
<br />
You can do it yourself by using <a href='delacct.php' class='altlink'>this form</a>.<br />
<br />
<br />
<b>So, what's MY ratio?</b><a name="userb" id="userb"></a><br />
<br />
For those with their own user accounts, your ratio=data uploaded/data downloaded. This site does not enforce users to maintain a minimum ratio. However, we would encourage users to upload as much as they download (a ratio of >1.0).
<br />
<br/>
Your ratio is located in the status bar on the top left of website.
<br />
<br/>
It's important to distinguish between your overall ratio and the individual ratio on each torrent
you may be seeding or leeching. The overall ratio takes into account the total uploaded and downloaded
from your account since you joined the site. The individual ratio takes into account those values for each torrent.<br />
<br />
You may see two symbols instead of a number: &quot;Inf.&quot;, which is just an abbreviation for Infinity, and
means that you have downloaded 0 bytes while uploading a non-zero amount (ul/dl becomes infinity); &quot;---&quot;,
which should be read as &quot;non-available&quot;, and shows up when you have both downloaded and uploaded 0 bytes
(ul/dl = 0/0 which is an indeterminate amount).<br />
<br />
<br />
<b>Why is my IP displayed on my details page?</b><a name="user5" id="user5"></a><br />
<br />
Only you and the site moderators can view your IP address and email. Regular users do not see that information.<br />
<br />
<br />
<b> My IP address is dynamic. How do I stay logged in?</b><a name="user7" id="user7"></a><br />
<br />
All you have to do is make sure you are logged in with your actual
IP when starting a torrent session. After that, even if the IP changes mid-session,
the seeding or leeching will continue and the statistics will update without any problem.<br />
<br />
<br />
<b>Why am I listed as not connectable? (And why should I care?)</b><a name="user8" id="user8"></a><br />
<br />
The tracker has determined that you are firewalled or NATed and cannot accept incoming connections.
<br />
<br />
This means that other peers in the swarm will be unable to connect to you, only you to them. Even worse,
if two peers are both in this state they will not be able to connect at all. This has obviously a
detrimental effect on the overall speed.
<br />
<br />
A simple way to solve the problem is to try using a different BitTorrent client. Some clients such as uTorrent and Deluge have better mechanisms for opening ports automatically or for still being able to download with closed ports.
<br />
<br />
The best solution involves opening the ports used for incoming connections
(the same range you defined in your client) on the firewall/router.
Check your router documentation (<a class='altlink' href="redir.php?url=http://portforward.com/">PortForward</a> is useful) or if at a large insitution you should contact your IT support.
<br />
<br />
<b>How do I add an avatar to my profile?</b><a name="userd"></a><br />
<br />
First, find an image that you like. Then you will have
to find a place to host it, such as our own <a class='altlink' href='bitbucket-upload.php'>BitBucket</a>.
(Other popular choices are <a class="altlink" href="http://photobucket.com/">Photobucket</a>,
<a class="altlink" href="http://uploadit.org/">Upload-It!</a> or
<a class="altlink" href="http://www.imageshack.us/">ImageShack</a>).
All that is left to do is copy the URL you were given when
uploading it to the avatar field in your <a class="altlink" href="my.php">profile</a>.<br />
If everything is allright you'll see it in your <a class="altlink" href="userdetails.php?id=<?php echo $CURUSER['id']?>">details page</a>.
<br />
</td>
</tr></table>
</td></tr></table>
<br />
<br />

<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<h2>What if I can't find the answer to my problem here?<a name="other" id="other"></a></h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'>
<br />
<ul>
<li>There is lots of information on the internet about torrents. <a href='http://www.google.com' class='altlink'>Google</a> can often help!
<li>Try posting in the <a class="altlink" href="forums.php">Forums</a>.</li>
<li>You can <a class='altlink' href='sendmessage.php?receiver=1' >send us a message</a> and we will try to respond quickly.</li>
</ul>
</td>
</tr></table>
<p align='right'><font size='1' color='#004E98'><b>FAQ edited by mlangill at 2010-05-07</b></font></p>
</td></tr></table>

<?php
stdfoot();
?>