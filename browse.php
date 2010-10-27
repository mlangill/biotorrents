<?php

ob_start("ob_gzhandler");

require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once "include/torrenttable_functions.php";
require_once "include/pager_functions.php";
require_once "include/search.php";

dbconn(false);

loggedinorreturn();

$cats = genrelist();
if(isset($_GET["search"])) {
$searchstr = unesc($_GET["search"]);
$cleansearchstr = searchfield($searchstr);
if (empty($cleansearchstr))
	unset($cleansearchstr);
}

list ($res,$wherecatina,$pager) = search($_GET, $CURUSER);



if (isset($cleansearchstr))
	stdhead("Search results for \"$searchstr\"");
else
	stdhead();

?>


<?php
//Morgan: Disable tag cloud
/*
echo '<div id="wrapper" style="width:90%;border:1px solid black;background-color:pink;">';
//print out the tag cloud
require_once "include/searchcloud_functions.php";
print cloud();
echo '</div>';
*/
?>
<table width='750' class='main' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>

<form method="get" action='browse.php'>

<p align="left">

Search:
<input type="text" name="search" size="40" value="" />
in
<select name="cat">
<option value="0">(all categories)</option>
<?php

//category drop down box
$cats = genrelist();
$catdropdown = "";
foreach ($cats as $cat) {
    $catdropdown .= "<option value=\"" . $cat["id"] . "\"";
    $getcat = (isset($_GET["cat"])?$_GET["cat"]:'');
    if ($cat["id"] == $getcat)
        $catdropdown .= " selected=\"selected\"";
    $catdropdown .= ">" . htmlspecialchars($cat["name"]) . "</option>\n";
}
echo $catdropdown,"</select>";

//dead checkbox
$deadchkbox = "<input type=\"checkbox\" name=\"incldead\" value=\"1\"";
if (isset($_GET["incldead"]))
    $deadchkbox .= " checked=\"checked\"";
$deadchkbox .= " /> including dead torrents\n";
//echo $deadchkbox;

echo " and ";
//license drop down box
$lics = licenselist();
$licdropdown = "<select name=\"lic\">";
$licdropdown .= "<option value=\"0\">(all licenses)</option>";
foreach ($lics as $lic) {
    $licdropdown .= "<option value=\"" . $lic["id"] . "\"";
    $getlic = (isset($_GET["lic"])?$_GET["lic"]:'');
    if ($lic["id"] == $getlic)
        $licdropdown .= " selected=\"selected\"";
    $licdropdown .= ">" . htmlspecialchars($lic["name"]) . "</option>\n";
}
echo $licdropdown,"</select>";
?>


<input type="submit" value="Search!" class="btn" />
</p>
</form>
</td></tr></table>
<br />
<form method="get" action="browse.php">
<table class='bottom'>
<tr>
<td class='bottom'>
	<table class='bottom'>
	<tr>

<?php
$i = 0;
foreach ($cats as $cat)
{
	$catsperrow = 7;
	print(($i && $i % $catsperrow == 0) ? "</tr><tr>" : "");
	print("<td class='bottom' style=\"padding-bottom: 2px;padding-left: 7px\"><input name='c".$cat['id']."' type=\"checkbox\" " . (in_array($cat['id'],$wherecatina) ? "checked='checked' " : "") . "value='1' /><a class='catlink' href='browse.php?cat=$cat[id]'>" . htmlspecialchars($cat['name']) . "</a></td>\n");
	$i++;
}

$alllink = "<div align='left'>(<a href='browse.php?all=1'><b>Show all</b></a>)</div>";

$ncats = count($cats);
$nrows = ceil($ncats/$catsperrow);
$lastrowcols = $ncats % $catsperrow;

if ($lastrowcols != 0)
{
	if ($catsperrow - $lastrowcols != 1)
		{
			print("<td class='bottom' rowspan='" . ($catsperrow  - $lastrowcols - 1) . "'>&nbsp;</td>");
		}
	print("<td class='bottom' style=\"padding-left: 5px\">$alllink</td>\n");
}

$selected = (isset($_GET["incldead"])) ? (int)$_GET["incldead"] : "";
?>
	</tr>
	</table>
</td>

<td class='bottom'>
<table class='main'>
	<tr>
		<td class='bottom' style="padding: 1px;padding-left: 10px">
			<select name='incldead'>
<option value="0">active</option>
<option value="1"<?php print($selected == 1 ? " selected='selected'" : ""); ?>>including dead</option>
<option value="2"<?php print($selected == 2 ? " selected='selected'" : ""); ?>>only dead</option>
			</select>
  	</td>
<?php
if ($ncats % $catsperrow == 0)
	print("<td class='bottom' style=\"padding-left: 15px\" rowspan='$nrows' valign='center' align='right'>$alllink</td>\n");
?>

  </tr>
  <tr>
  	<td class='bottom' style="padding: 1px;padding-left: 10px">
  	<div align='center'>
  		<input type="submit" class='btn' value="Go!"/>
	</div>
	
  	</td>
  </tr>
  </table>
</td>
</tr>
</table>
</form>

<?php

if (isset($cleansearchstr))
print("<h2>Search results for \"" . htmlentities($searchstr, ENT_QUOTES) . "\"</h2>\n");

if ($res) {
 
$rss_query="";
if($_SERVER['QUERY_STRING']){
	$rss_query = "?".$_SERVER['QUERY_STRING'];
}
print("<a href=\"rss.php".$rss_query."\" >");
print("<img align=center border=0 src=\"pic/rss.png\" alt=\"RSS\" /></a>");


	print($pager['pagertop']);


	torrenttable($res);

	print($pager['pagerbottom']);
}
else {
	if (isset($cleansearchstr)) {
		print("<h2>Nothing found!</h2>\n");
		print("<p>Try again with a refined search string.</p>\n");
	}
	else {
		print("<h2>Nothing here!</h2>\n");
		#print("<p>Sorry pal :(</p>\n");
	}
}

stdfoot();

?>