<?php

require_once("include/bittorrent.php");

dbconn();
loggedinorreturn();

if( get_user_class() != UC_SYSOP )
	exit();
	
docleanup();

print("Done");

?>
