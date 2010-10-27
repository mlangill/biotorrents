<?php
require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn();
loggedinorreturn();

stdhead("Search");
?>
<table width='750' class='main' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>

<form method="get" action='browse.php'>
<p align="center">
Search:
<input type="text" name="search" size="40" value="" />
in
<select name="cat">
<option value="0">(all types)</option>
<?php


$cats = genrelist();
$catdropdown = "";
foreach ($cats as $cat) {
    $catdropdown .= "<option value=\"" . $cat["id"] . "\"";
    $getcat = (isset($_GET["cat"])?$_GET["cat"]:'');
    if ($cat["id"] == $getcat)
        $catdropdown .= " selected=\"selected\"";
    $catdropdown .= ">" . htmlspecialchars($cat["name"]) . "</option>\n";
}

$deadchkbox = "<input type=\"checkbox\" name=\"incldead\" value=\"1\"";
if (isset($_GET["incldead"]))
    $deadchkbox .= " checked=\"checked\"";
$deadchkbox .= " /> including dead torrents\n";

?>
<?php echo $catdropdown ?>
</select>
<?php echo $deadchkbox ?>
<input type="submit" value="Search!" class="btn" />
</p>
</form>
</td></tr></table>

<?php

stdfoot();

?>