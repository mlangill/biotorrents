<?php

ob_start("ob_gzhandler");

require_once("include/bittorrent.php");
require_once "include/user_functions.php";
//require_once "include/torrenttable_functions.php";
//require_once "include/pager_functions.php";

dbconn(false);

loggedinorreturn();


if(isset($_POST["search"]) && !empty($_POST['search'])) {
	
	$cleansearchstr = sqlesc(searchfield($_POST['search']));
	print $cleansearchstr;
	}
	else
	stderr("Opps", "Nuffin 'ere!");


$query = mysql_query("SELECT id, filename, MATCH (filename)
						AGAINST (".$cleansearchstr.") AS score
						FROM files WHERE MATCH (filename)
						AGAINST (".$cleansearchstr." IN BOOLEAN MODE)");

if(mysql_num_rows($query) == 0)
	stderr("Error", "Nothing found");
	
	while($row = mysql_fetch_assoc($query)) {
	
		print '<pre>'.$row['id']."-".htmlspecialchars($row['filename'])."-".$row['score'].'</pre>';
	}
?>