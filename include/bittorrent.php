<?php

require_once("include/config.php");
require_once("secrets.php");
require_once("cleanup.php");

/* Compare php version for date/time stuff etc! */
	if (version_compare(PHP_VERSION, "5.1.0RC1", ">="))
		date_default_timezone_set('Europe/London');

/**** validip/getip courtesy of manolete <manolete@myway.com> ****/

// IP Validation
function validip($ip)
{
	if (!empty($ip) && $ip == long2ip(ip2long($ip)))
	{
		// reserved IANA IPv4 addresses
		// http://www.iana.org/assignments/ipv4-address-space
		$reserved_ips = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
		);

		foreach ($reserved_ips as $r)
		{
				$min = ip2long($r[0]);
				$max = ip2long($r[1]);
				if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}
		return true;
	}
	else return false;
}

// Patched function to detect REAL IP address if it's valid
function getip() {
   if (isset($_SERVER)) {
     if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && validip($_SERVER['HTTP_X_FORWARDED_FOR'])) {
       $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
     } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && validip($_SERVER['HTTP_CLIENT_IP'])) {
       $ip = $_SERVER['HTTP_CLIENT_IP'];
     } else {
       $ip = $_SERVER['REMOTE_ADDR'];
     }
   } else {
     if (getenv('HTTP_X_FORWARDED_FOR') && validip(getenv('HTTP_X_FORWARDED_FOR'))) {
       $ip = getenv('HTTP_X_FORWARDED_FOR');
     } elseif (getenv('HTTP_CLIENT_IP') && validip(getenv('HTTP_CLIENT_IP'))) {
       $ip = getenv('HTTP_CLIENT_IP');
     } else {
       $ip = getenv('REMOTE_ADDR');
     }
   }

   return $ip;
 }

function dbconn($autoclean = false)
{
    global $mysql_host, $mysql_user, $mysql_pass, $mysql_db;

    if (!@mysql_connect($mysql_host, $mysql_user, $mysql_pass))
    {
	  switch (mysql_errno())
	  {
		case 1040:
		case 2002:
			if ($_SERVER['REQUEST_METHOD'] == "GET")
				die("<html><head><meta http-equiv='refresh' content=\"5 $_SERVER[REQUEST_URI]\"></head><body><table border='0' width='100%' height='100%'><tr><td><h3 align='center'>The server load is very high at the moment. Retrying, please wait...</h3></td></tr></table></body></html>");
			else
				die("Too many users. Please press the Refresh button in your browser to retry.");
        default:
    	    die("[" . mysql_errno() . "] dbconn: mysql_connect: " . mysql_error());
      }
    }
    mysql_select_db($mysql_db)
        or die('dbconn: mysql_select_db: ' . mysql_error());

    userlogin();

    if ($autoclean)
        register_shutdown_function("autoclean");
}


function userlogin() {
    global $SITE_ONLINE;
    unset($GLOBALS["CURUSER"]);

    $ip = getip();
	$nip = ip2long($ip);
/*    $res = mysql_query("SELECT * FROM bans WHERE $nip >= first AND $nip <= last") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) > 0)
    {
      header("HTTP/1.0 403 Forbidden");
      print("<html><body><h1>403 Forbidden</h1>Unauthorized IP address.</body></html>\n");
      die;
    }

    require_once "cache/bans_cache.php";
    if(count($bans) > 0)
    {
      foreach($bans as $k) {
        if($nip >= $k['first'] && $nip <= $k['last']) {
        header("HTTP/1.0 403 Forbidden");
        print("<html><body><h1>403 Forbidden</h1>Unauthorized IP address.</body></html>\n");
        exit();
        }
      }
      unset($bans);
    }
*/
    if (!$SITE_ONLINE) return;
    
    #If not cookie set just use guest account
    #Morgan
    if (empty($_COOKIE["uid"]) || empty($_COOKIE["pass"])){
	$_COOKIE["uid"]=4;
	$_COOKIE["pass"]='65c22bd4088400d7dd893291e787b026';
    }
    $id = 0 + $_COOKIE["uid"];
    if (!$id || strlen($_COOKIE["pass"]) != 32)
        return;
    $res = mysql_query("SELECT * FROM users WHERE id = $id AND enabled='yes' AND status = 'confirmed'");// or die(mysql_error());
    $row = mysql_fetch_assoc($res);
    if (!$row)
        return;
    //$sec = hash_pad($row["secret"]);
    if ($_COOKIE["pass"] !== $row["passhash"])
        return;
    mysql_query("UPDATE users SET last_access='" . TIME_NOW . "', ip=".sqlesc($ip)." WHERE id=" . $row["id"]);// or die(mysql_error());
    $row['ip'] = $ip;
    $GLOBALS["CURUSER"] = $row;
}

function autoclean() {
    global $autoclean_interval;

    $now = time();
    //$docleanup = 0;

    $res = mysql_query("SELECT value_u FROM avps WHERE arg = 'lastcleantime'");
    $row = mysql_fetch_array($res);
    if (!$row) {
        mysql_query("INSERT INTO avps (arg, value_u) VALUES ('lastcleantime',$now)");
        return;
    }
    $ts = $row[0];
    if ($ts + $autoclean_interval > $now)
        return;
    mysql_query("UPDATE avps SET value_u=$now WHERE arg='lastcleantime' AND value_u = $ts");
    if (!mysql_affected_rows())
        return;

    
    docleanup();
}

function unesc($x) {
    if (get_magic_quotes_gpc())
        return stripslashes($x);
    return $x;
}

function mksize($bytes)
{
	if ($bytes < 1000 * 1024)
		return number_format($bytes / 1024, 2) . " kB";
	elseif ($bytes < 1000 * 1048576)
		return number_format($bytes / 1048576, 2) . " MB";
	elseif ($bytes < 1000 * 1073741824)
		return number_format($bytes / 1073741824, 2) . " GB";
	else
		return number_format($bytes / 1099511627776, 2) . " TB";
}
/*
function mksizeint($bytes)
{
	$bytes = max(0, $bytes);
	if ($bytes < 1000)
		return floor($bytes) . " B";
	elseif ($bytes < 1000 * 1024)
		return floor($bytes / 1024) . " kB";
	elseif ($bytes < 1000 * 1048576)
		return floor($bytes / 1048576) . " MB";
	elseif ($bytes < 1000 * 1073741824)
		return floor($bytes / 1073741824) . " GB";
	else
		return floor($bytes / 1099511627776) . " TB";
}
*/

function mkprettytime($s) {
    if ($s < 0)
        $s = 0;
    $t = array();
    foreach (array("60:sec","60:min","24:hour","0:day") as $x) {
        $y = explode(":", $x);
        if ($y[0] > 1) {
            $v = $s % $y[0];
            $s = floor($s / $y[0]);
        }
        else
            $v = $s;
        $t[$y[1]] = $v;
    }

    if ($t["day"])
        return $t["day"] . "d " . sprintf("%02d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
    if ($t["hour"])
        return sprintf("%d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
//    if ($t["min"])
        return sprintf("%d:%02d", $t["min"], $t["sec"]);
//    return $t["sec"] . " secs";
}

function mkglobal($vars) {
    if (!is_array($vars))
        $vars = explode(":", $vars);
    foreach ($vars as $v) {
        if (isset($_GET[$v]))
            $GLOBALS[$v] = unesc($_GET[$v]);
        elseif (isset($_POST[$v]))
            $GLOBALS[$v] = unesc($_POST[$v]);
        else
            return 0;
    }
    return 1;
}


function validfilename($name) {
    return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
}

function validemail($email) {
    return preg_match('/^[\w.-]+@([\w.-]+\.)+[a-z]{2,6}$/is', $email);
}

function sqlesc($x) {
    return "'".mysql_real_escape_string($x)."'";
}

function sqlwildcardesc($x) {
    return str_replace(array("%","_"), array("\\%","\\_"), mysql_real_escape_string($x));
}
/*
function urlparse($m) {
    $t = $m[0];
    if (preg_match(',^\w+://,', $t))
        return "<a href=\"$t\">$t</a>";
    return "<a href=\"http://$t\">$t</a>";
}

function parsedescr($d, $html) {
    if (!$html)
    {
      $d = htmlspecialchars($d);
      $d = str_replace("\n", "\n<br>", $d);
    }
    return $d;
}
*/
function stdhead($title = "", $msgalert = true) {
    global $CURUSER, $SITE_ONLINE, $SITENAME, $pic_base_url, $stylesheet;

  if (!$SITE_ONLINE)
    die("Site is down for maintenance, please check back again later... thanks<br>");

    //header("Content-Type: text/html; charset=iso-8859-1");
    //header("Pragma: No-cache");
    if ($title == "")
        $title = $SITENAME .(isset($_GET['tbv'])?" (".TBVERSION.")":'');
    else
        $title = $SITENAME .(isset($_GET['tbv'])?" (".TBVERSION.")":''). " :: " . htmlspecialchars($title);
        
  if ($CURUSER)
  {
    /*
    $ss_a = @mysql_fetch_array(@sql_query("select uri from stylesheets where id=" . $CURUSER["stylesheet"]));

    if ($ss_a) $ss_uri = $ss_a["uri"];
    */
	$stylesheet = "{$CURUSER['stylesheet']}.css";
  }
  
  if ($msgalert && $CURUSER)
  {
    $res = mysql_query("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " && unread='yes'") or sqlerr(__FILE__,__LINE__);
    $arr = mysql_fetch_row($res);
    $unread = $arr[0];
  }

$rss_query="";
if($_SERVER['QUERY_STRING']){
	$rss_query = "?".$_SERVER['QUERY_STRING'];
}
?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>

			<meta name="generator" content="BioTorrents.net" />
			<meta http-equiv="Content-Language" content="en-us" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<meta name="MSSmartTagsPreventParsing" content="TRUE" />
			<link rel="alternate" type="application/rss+xml" title="RSS"
      href="http://biotorrents.net/rss.php<?php echo $rss_query?>"
			<title></title>
			<link rel="stylesheet" href="<?php echo $stylesheet?>" type="text/css" />
		</head>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-12733868-2");
pageTracker._trackPageview();
} catch(err) {}</script>
<body>

<table width='100%' cellspacing='0' cellpadding='0' style='background: transparent'>
<tr>

<td class='clear'>
<div id="logostrip">
<a href="<?php echo $BASEURL?>"><img src="<?php echo $pic_base_url?>logo3.gif" alt='' /></a>

<!--<a href='donate.php'><img src="./pic/x-click-but04.gif" border="0" alt="Make a donation" style='margin-top: 5px' /></a>-->
</div>
</td>

</tr></table>

<table class='mainouter' width="100%" border="1" cellspacing="0" cellpadding="10">
<!-- STATUSBAR -->
<?php
print StatusBar();
?>
<!-- MENU -->


<tr><td class='outer'>
<div id="submenu">

<?php if ($CURUSER && $CURUSER['id'] != 4) { ?>
<div class="tb-top-left-link">
<a href='about.php'>About</a>
<a href='browse.php'>Browse</a>
<a href='upload.php'>Upload</a>
<a href='forums.php'>Forums</a>
<a href='topten.php'>Top 10</a>
<a href='log.php'>Log</a>
<a href='faq.php'>FAQ</a>
<a href='links.php'>Links</a>
<a href='staff.php'>Contact Us</a>
</div>
<div class="tb-top-right-link">
<a href='my.php'>Profile</a>
<a href='logout.php'>Logout</a>
</div>
<?php } else { ?>
<div class="tb-top-left-link">
<a href='about.php'>About</a>
<a href='browse.php'>Browse</a>
<a href='upload.php'>Upload</a>
<a href='forums.php'>Forums</a>
<a href='topten.php'>Top 10</a>
<a href='log.php'>Log</a>
<a href='faq.php'>FAQ</a>
<a href='links.php'>Links</a>
<a href='staff.php'>Contact Us</a>
</div>
<div class="tb-top-right-link">
<a href='login.php'>Login</a>
<a href='signup.php'>Signup</a>
<a href='recover.php'>Recover Account</a>
</div>
<?php } ?>

</div>
</td>
</tr>
<tr><td align='center' class='outer' style="padding-top: 20px; padding-bottom: 20px">
<?php

if (isset($unread) && !empty($unread))
{
  print("<p><table border='0' cellspacing='0' cellpadding='10' bgcolor='red'><tr><td style='padding: 10px; background: red'>\n");
  print("<b><a href='messages.php'><font color='white'>You have $unread new message" . ($unread > 1 ? "s" : "") . "!</font></a></b>");
  print("</td></tr></table></p>\n");
}

} // stdhead

function stdfoot() {
  //global $pic_base_url;
  print("</td></tr></table>\n");
  print("</body></html>\n");
}

function genbark($x,$y) {
    stdhead($y);
    print("<h2>" . htmlspecialchars($y) . "</h2>\n");
    print("<p>" . htmlspecialchars($x) . "</p>\n");
    stdfoot();
    exit();
}

function mksecret($len = 20) {
    $ret = "";
    for ($i = 0; $i < $len; $i++)
        $ret .= chr(mt_rand(0, 255));
    return $ret;
}

function httperr($code = 404) {
    header("HTTP/1.0 404 Not found");
    print("<h1>Not Found</h1>\n");
    print("<p>Sorry pal :(</p>\n");
    exit();
}

/*
function gmtime()
{
    return strtotime(get_date_time());
}
*/
/*
function logincookie($id, $password, $secret, $updatedb = 1, $expires = 0x7fffffff) {
    $md5 = md5($secret . $password . $secret);
    setcookie("uid", $id, $expires, "/");
    setcookie("pass", $md5, $expires, "/");

    if ($updatedb)
        mysql_query("UPDATE users SET last_login = NOW() WHERE id = $id");
}
*/

function logincookie($id, $passhash, $updatedb = 1, $expires = 0x7fffffff)
{
	setcookie("uid", $id, $expires, "/");
	setcookie("pass", $passhash, $expires, "/");

  if ($updatedb)
  	mysql_query("UPDATE users SET last_login = ".TIME_NOW." WHERE id = $id");
}


function logoutcookie() {
    setcookie("uid", "", 0x7fffffff, "/");
    setcookie("pass", "", 0x7fffffff, "/");
}

function loggedinorreturn() {
    global $CURUSER, $BASEURL;
    if (!$CURUSER) {
        header("Location: $BASEURL/login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]));
        exit();
    }
}


/*
function downloaderdata($res) {
    $rows = array();
    $ids = array();
    $peerdata = array();
    while ($row = mysql_fetch_assoc($res)) {
        $rows[] = $row;
        $id = $row["id"];
        $ids[] = $id;
        $peerdata[$id] = array(downloaders => 0, seeders => 0, comments => 0);
    }

    if (count($ids)) {
        $allids = implode(",", $ids);
        $res = mysql_query("SELECT COUNT(*) AS c, torrent, seeder FROM peers WHERE torrent IN ($allids) GROUP BY torrent, seeder");
        while ($row = mysql_fetch_assoc($res)) {
            if ($row["seeder"] == "yes")
                $key = "seeders";
            else
                $key = "downloaders";
            $peerdata[$row["torrent"]][$key] = $row["c"];
        }
        $res = mysql_query("SELECT COUNT(*) AS c, torrent FROM comments WHERE torrent IN ($allids) GROUP BY torrent");
        while ($row = mysql_fetch_assoc($res)) {
            $peerdata[$row["torrent"]]["comments"] = $row["c"];
        }
    }

    return array($rows, $peerdata);
}
*/


function searchfield($s) {
    return preg_replace(array('/[^a-z0-9]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
}

function genrelist() {
    $ret = array();
    $res = mysql_query("SELECT id, name FROM categories ORDER BY name");
    while ($row = mysql_fetch_array($res))
        $ret[] = $row;
    return $ret;
}

#Morgan: added license functionality
function licenselist() {
   $ret = array();
    $res = mysql_query("SELECT id, name, description FROM licenses ORDER BY id ");
    while ($row = mysql_fetch_array($res))
        $ret[] = $row;
    return $ret;
}

#Morgan: added version functionality
function versionlist() {
   $ret = array();
    $res = mysql_query("SELECT id, name FROM versions ORDER BY name");
    while ($row = mysql_fetch_array($res))
        $ret[] = $row;
    return $ret;
}
function get_version_id_for_torrent($id, $id2){
	 $version_id=0;
	 if($id){
		$results = mysql_query("SELECT version FROM torrents WHERE id=$id");
	 	$row = mysql_fetch_array($results);
		if($id2){
			$results2 = mysql_query("SELECT version FROM torrents WHERE id=$id2");
	 		$row2 = mysql_fetch_array($results2);
			if(($row2["version"] && $row["version"]) || ($row2["version"] && !$row["version"])){
				$version_id=$row2["version"];
				mysql_query("UPDATE torrents SET version=$version_id WHERE id=$id");
			}elseif($row["version"]){
				$version_id=$row["version"];
			}else{
				#create new version id
				mysql_query("INSERT INTO versions () VALUES ()");
				$version_id=mysql_insert_id();

				mysql_query("UPDATE torrents SET version=$version_id WHERE id=$id");
			}
		}else{
		
		if($row["version"]){
			$version_id=$row["version"];
		}else{
			#create new version id
			mysql_query("INSERT INTO versions () VALUES ()");
			$version_id=mysql_insert_id();
		
			#update the version for the chosen torrent
			mysql_query("UPDATE torrents SET version=$version_id WHERE id=$id");
		}
		}
	}
	return $version_id;
}

function torrentlist() {
   $ret = array();
    $res = mysql_query("SELECT id, name FROM torrents ORDER BY id DESC");
    while ($row = mysql_fetch_array($res))
        $ret[] = $row;
    return $ret;
}


function get_row_count($table, $suffix = "")
{
  if ($suffix)
    $suffix = " $suffix";
  ($r = mysql_query("SELECT COUNT(*) FROM $table$suffix")) or die(mysql_error());
  ($a = mysql_fetch_row($r)) or die(mysql_error());
  return $a[0];
}

function stdmsg($heading, $text)
{
  print("<table class='main' width='750' border='0' cellpadding='0' cellspacing='0'><tr><td class='embedded'>\n");
  if ($heading)
    print("<h2>$heading</h2>\n");
  print("<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'>\n");
  print($text . "</td></tr></table></td></tr></table>\n");
}


function stderr($heading, $text)
{
  stdhead();
  stdmsg($heading, $text);
  stdfoot();
  die;
}
/*
function sqlerr($file = '', $line = '')
{
  print("<table border=0 bgcolor=blue align=left cellspacing=0 cellpadding=10 style='background: blue'>" .
    "<tr><td class=embedded><font color=white><h1>SQL Error</h1>\n" .
  "<b>" . mysql_error() . ($file != '' && $line != '' ? "<p>in $file, line $line</p>" : "") . "</b></font></td></tr></table>");
  die;
}
*/
	
// Basic MySQL error handler

function sqlerr($file = '', $line = '') {
    global $sql_error_log, $CURUSER;
    
		$the_error    = mysql_error();
		$the_error_no = mysql_errno();

    	if ( SQL_DEBUG == 0 )
    	{
			exit();
    	}
     	else if ( $sql_error_log AND SQL_DEBUG == 1 )
		{
			$_error_string  = "\n===================================================";
			$_error_string .= "\n Date: ". date( 'r' );
			$_error_string .= "\n Error Number: " . $the_error_no;
			$_error_string .= "\n Error: " . $the_error;
			$_error_string .= "\n IP Address: " . $_SERVER['REMOTE_ADDR'];
			$_error_string .= "\n in file ".$file." on line ".$line;
			$_error_string .= "\n URL:".$_SERVER['REQUEST_URI'];
			$_error_string .= "\n Username: {$CURUSER['username']}[{$CURUSER['id']}]";
			
			if ( $FH = @fopen( $sql_error_log, 'a' ) )
			{
				@fwrite( $FH, $_error_string );
				@fclose( $FH );
			}
			
			print "<html><head><title>MySQL Error</title>
					<style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style></head><body>
		    		   <blockquote><h1>MySQL Error</h1><b>There appears to be an error with the database.</b><br />
		    		   You can try to refresh the page by clicking <a href=\"javascript:window.location=window.location;\">here</a>
				  </body></html>";
		}
		else
		{
    		$the_error = "\nSQL error: ".$the_error."\n";
	    	$the_error .= "SQL error code: ".$the_error_no."\n";
	    	$the_error .= "Date: ".date("l dS \of F Y h:i:s A");
    	
	    	$out = "<html>\n<head>\n<title>MySQL Error</title>\n
	    		   <style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style>\n</head>\n<body>\n
	    		   <blockquote>\n<h1>MySQL Error</h1><b>There appears to be an error with the database.</b><br />
	    		   You can try to refresh the page by clicking <a href=\"javascript:window.location=window.location;\">here</a>.
	    		   <br /><br /><b>Error Returned</b><br />
	    		   <form name='mysql'><textarea rows=\"15\" cols=\"60\">".htmlentities($the_error, ENT_QUOTES)."</textarea></form><br>We apologise for any inconvenience</blockquote></body></html>";
    		   
    
	       	print $out;
		}
		
        exit();
}
    
/*    
// Returns the current time in GMT in MySQL compatible format.
function get_date_time($timestamp = 0)
{
  if ($timestamp)
    return date("Y-m-d H:i:s", $timestamp);
  else
    return gmdate("Y-m-d H:i:s");
}
*/

function get_dt_num()
{
  return gmdate("YmdHis");
}



function write_log($text)
{
  $text = sqlesc($text);
  $added = TIME_NOW;
  mysql_query("INSERT INTO sitelog (added, txt) VALUES($added, $text)") or sqlerr(__FILE__, __LINE__);
}


function sql_timestamp_to_unix_timestamp($s)
{
  return mktime(substr($s, 11, 2), substr($s, 14, 2), substr($s, 17, 2), substr($s, 5, 2), substr($s, 8, 2), substr($s, 0, 4));
}

/*
function get_elapsed_time($ts)
{
  $mins = floor((gmtime() - $ts) / 60);
  $hours = floor($mins / 60);
  $mins -= $hours * 60;
  $days = floor($hours / 24);
  $hours -= $days * 24;
  $weeks = floor($days / 7);
  $days -= $weeks * 7;
//  $t = "";
  if ($weeks > 0)
    return "$weeks week" . ($weeks > 1 ? "s" : "");
  if ($days > 0)
    return "$days day" . ($days > 1 ? "s" : "");
  if ($hours > 0)
    return "$hours hour" . ($hours > 1 ? "s" : "");
  if ($mins > 0)
    return "$mins min" . ($mins > 1 ? "s" : "");
  return "< 1 min";
}
*/


function unixstamp_to_human( $unix=0 )
    {
    	$offset = get_time_offset();
    	$tmp    = gmdate( 'j,n,Y,G,i', $unix + $offset );
    	
    	list( $day, $month, $year, $hour, $min ) = explode( ',', $tmp );
  
    	return array( 'day'    => $day,
                    'month'  => $month,
                    'year'   => $year,
                    'hour'   => $hour,
                    'minute' => $min );
    }
    


function get_time_offset() {
    
    	global $CURUSER, $CONFIG_INFO;
    	$r = 0;
    	
    	$r = ( ($CURUSER['time_offset'] != "") ? $CURUSER['time_offset'] : $CONFIG_INFO['time_offset'] ) * 3600;
			
      if ( $CONFIG_INFO['time_adjust'] )
      {
        $r += ($CONFIG_INFO['time_adjust'] * 60);
      }
      
      if ( $CURUSER['dst_in_use'] )
      {
        $r += 3600;
      }
        
        return $r;
}
    

function get_date($date, $method, $norelative=0, $full_relative=0)
    {
        global $CONFIG_INFO;
        
        static $offset_set = 0;
        static $today_time = 0;
        static $yesterday_time = 0;
        $time_options = array( 
        'JOINED' => $CONFIG_INFO['time_joined'],
        'SHORT'  => $CONFIG_INFO['time_short'],
				'LONG'   => $CONFIG_INFO['time_long'],
				'TINY'   => $CONFIG_INFO['time_tiny'] ? $CONFIG_INFO['time_tiny'] : 'j M Y - G:i',
				'DATE'   => $CONFIG_INFO['time_date'] ? $CONFIG_INFO['time_date'] : 'j M Y'
				);
        
        if ( ! $date )
        {
            return '--';
        }
        
        if ( empty($method) )
        {
        	$method = 'LONG';
        }
        
        if ($offset_set == 0)
        {
        	$GLOBALS['offset'] = get_time_offset();
			
          if ( $CONFIG_INFO['time_use_relative'] )
          {
            $today_time     = gmdate('d,m,Y', ( time() + $GLOBALS['offset']) );
            $yesterday_time = gmdate('d,m,Y', ( (time() - 86400) + $GLOBALS['offset']) );
          }	
        
          $offset_set = 1;
        }
        
        if ( $CONFIG_INFO['time_use_relative'] == 3 )
        {
        	$full_relative = 1;
        }
        
        if ( $full_relative and ( $norelative != 1 ) )
        {
          $diff = time() - $date;
          
          if ( $diff < 3600 )
          {
            if ( $diff < 120 )
            {
              return '< 1 minute ago';
            }
            else
            {
              return sprintf( '%s minutes ago', intval($diff / 60) );
            }
          }
          else if ( $diff < 7200 )
          {
            return '< 1 hour ago';
          }
          else if ( $diff < 86400 )
          {
            return sprintf( '%s hours ago', intval($diff / 3600) );
          }
          else if ( $diff < 172800 )
          {
            return '< 1 day ago';
          }
          else if ( $diff < 604800 )
          {
            return sprintf( '%s days ago', intval($diff / 86400) );
          }
          else if ( $diff < 1209600 )
          {
            return '< 1 week ago';
          }
          else if ( $diff < 3024000 )
          {
            return sprintf( '%s weeks ago', intval($diff / 604900) );
          }
          else
          {
            return gmdate($time_options[$method], ($date + $GLOBALS['offset']) );
          }
        }
        else if ( $CONFIG_INFO['time_use_relative'] and ( $norelative != 1 ) )
        {
          $this_time = gmdate('d,m,Y', ($date + $GLOBALS['offset']) );
          
          if ( $CONFIG_INFO['time_use_relative'] == 2 )
          {
            $diff = time() - $date;
          
            if ( $diff < 3600 )
            {
              if ( $diff < 120 )
              {
                return '< 1 minute ago';
              }
              else
              {
                return sprintf( '%s minutes ago', intval($diff / 60) );
              }
            }
          }
          
            if ( $this_time == $today_time )
            {
              return str_replace( '{--}', 'Today', gmdate($CONFIG_INFO['time_use_relative_format'], ($date + $GLOBALS['offset']) ) );
            }
            else if  ( $this_time == $yesterday_time )
            {
              return str_replace( '{--}', 'Yesterday', gmdate($CONFIG_INFO['time_use_relative_format'], ($date + $GLOBALS['offset']) ) );
            }
            else
            {
              return gmdate($time_options[$method], ($date + $GLOBALS['offset']) );
            }
        }
        else
        {
          return gmdate($time_options[$method], ($date + $GLOBALS['offset']) );
        }
}


function hash_pad($hash) {
    return str_pad($hash, 20);
}


function StatusBar() {

	global $CURUSER;
	
	if (!$CURUSER || $CURUSER['id'] == 4)
		return "<tr><td colspan='2'>You are using the guest account. Please <a class='morgan' href='login.php'>Log In</a></u> or <a class='morgan' href='signup.php'>Sign Up</a> to enjoy all features of BioTorrents!</td></tr>";


	$upped = mksize($CURUSER['uploaded']);
	
	$downed = mksize($CURUSER['downloaded']);
	
	$ratio = $CURUSER['downloaded'] > 0 ? $CURUSER['uploaded']/$CURUSER['downloaded'] : 0;
	
	$ratio = number_format($ratio, 2);

	$IsDonor = '';
	if ($CURUSER['donor'] == "yes")
	
	$IsDonor = "<img src='pic/star.gif' alt='donor' title='donor' />";


	$warn = '';
	if ($CURUSER['warned'] == "yes")
	
	$warn = "<img src='pic/warned.gif' alt='warned' title='warned' />";
	
	$res1 = mysql_query("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " AND unread='yes'") or print(mysql_error());
	
	$arr1 = mysql_fetch_row($res1);
	
	$unread = $arr1[0];
	
	$inbox = ($unread == 1 ? "$unread&nbsp;New Message" : "$unread&nbsp;New Messages");

	
	$res2 = mysql_query("SELECT seeder, COUNT(*) AS pCount FROM peers WHERE userid=".$CURUSER['id']." GROUP BY seeder") or print(mysql_error());
	
	$seedleech = array('yes' => '0', 'no' => '0');
	
	while( $row = mysql_fetch_assoc($res2) ) {
		if($row['seeder'] == 'yes')
			$seedleech['yes'] = $row['pCount'];
		else
			$seedleech['no'] = $row['pCount'];
		
	}
	
/////////////// REP SYSTEM /////////////
//$CURUSER['reputation'] = 49;

	//$member_reputation = get_reputation($CURUSER, 1);
////////////// REP SYSTEM END //////////

	$StatusBar = '';
		$StatusBar = "<tr>".

		"<td colspan='2' style='padding: 2px;'>".

		"<div id='statusbar'>".
		"<p class='home' style='color:black;'>Welcome back, <a href='userdetails.php?id=".$CURUSER['id']."'>".$CURUSER['username']."</a>".
		  
		"$IsDonor$warn&nbsp; [<a href='logout.php'>logout</a>]</p>". "<p>".date(DATE_RFC822)."</p>";



		$StatusBar .= "".
		"<p class='home' style='color:black;'>Ratio:$ratio".
		"&nbsp;&nbsp;Uploaded:$upped".
		"&nbsp;&nbsp;Downloaded:$downed".
		
		"&nbsp;&nbsp;Active Torrents:&nbsp;<img alt='Torrents seeding' title='Torrents seeding' src='pic/arrowup.gif' />&nbsp;{$seedleech['yes']}".
		
		"&nbsp;&nbsp;<img alt='Torrents leeching' title='Torrents leeching' src='pic/arrowdown.gif' />&nbsp;{$seedleech['no']}</p>";
		

	$StatusBar .= "<p>".


	"<a href='messages.php'>$inbox</a>".
	"</p></div></td></tr>";
	
	return $StatusBar;

}


?>
