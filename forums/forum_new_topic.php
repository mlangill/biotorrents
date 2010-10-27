<?php

if ( ! defined( 'IN_TBDEV_FORUM' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}



    $forumid = (int)$_GET["forumid"];

    if (!is_valid_id($forumid))
      header("Location: $BASEURL/forums.php");

	

    stdhead("New topic");

    begin_main_frame();

    insert_compose_frame($forumid);

    end_main_frame();

    stdfoot();

    die;

?>