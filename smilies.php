<?php
require "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/html_functions.php";
require_once "include/emoticons.php";

dbconn(false);
loggedinorreturn();

stdhead();
begin_main_frame();
insert_smilies_frame();
end_main_frame();
stdfoot();
?>