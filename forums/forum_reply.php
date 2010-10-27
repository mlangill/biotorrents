<?php

if ( ! defined( 'IN_TBDEV_FORUM' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}


  //-------- Action: Reply
if ($action == "reply")
  {
    $topicid = (int)$_GET["topicid"];

    if (!is_valid_id($topicid))
      header("Location: $BASEURL/forums.php");

    stdhead("Post reply");

    begin_main_frame();

    insert_compose_frame($topicid, false);

    end_main_frame();

    stdfoot();

    die;
}

  //-------- Action: Quote

if ($action == "quotepost")
	{
		$topicid = (int)$_GET["topicid"];

		if (!is_valid_id($topicid))
			header("Location: $BASEURL/forums.php");

    stdhead("Post reply");

    begin_main_frame();

    insert_compose_frame($topicid, false, true);

    end_main_frame();

    stdfoot();

    die;
  }

?>