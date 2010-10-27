<?php 

require_once "bittorrent.php";
require_once "user_functions.php";
require_once "torrenttable_functions.php";
require_once "pager_functions.php";


function search($_GET,$CURUSER){


$cats = genrelist();

if(isset($_GET["search"])) {
$searchstr = unesc($_GET["search"]);
$cleansearchstr = searchfield($searchstr);
if (empty($cleansearchstr))
	unset($cleansearchstr);
}

$orderby = "ORDER BY torrents.id DESC";

$addparam = "";
$wherea = array();
$wherecatina = array();

if (isset($_GET["incldead"]) &&  $_GET["incldead"] == 1)
{
	$addparam .= "incldead=1&amp;";
	if (!isset($CURUSER) || get_user_class() < UC_ADMINISTRATOR)
		$wherea[] = "banned != 'yes'";
}
else
	if (isset($_GET["incldead"]) && $_GET["incldead"] == 2)
{
	$addparam .= "incldead=2&amp;";
		$wherea[] = "visible = 'no'";
}
	else
		$wherea[] = "visible = 'yes'";

$category = (isset($_GET["cat"])) ? (int)$_GET["cat"] : false;

$license =  (isset($_GET["lic"])) ? (int)$_GET["lic"] : false;

$version =  (isset($_GET["ver"])) ? (int)$_GET["ver"] : false;

$user = (isset($_GET["user"])) ? (int)$_GET["user"] : false;

$all = isset($_GET["all"]) ? $_GET["all"] : false;

$page_limit= isset($_GET["page_limit"]) ? $_GET["page_limit"] : false;

if (!$all)
	if (!$_GET && $CURUSER["notifs"])
	{
	  $all = True;
	  foreach ($cats as $cat)
	  {
	    $all &= $cat['id'];
	    if (strpos($CURUSER["notifs"], "[cat" . $cat['id'] . "]") !== False)
	    {
	      $wherecatina[] = $cat['id'];
	      $addparam .= "c{$cat['id']}=1&amp;";
	    }
	  }
	}
	elseif ($category)
	{
	  if (!is_valid_id($category))
	    stderr("Error", "Invalid category ID.");
	  $wherecatina[] = $category;
	  $addparam .= "cat=$category&amp;";
	}
	else
	{
	  $all = True;
	  foreach ($cats as $cat)
	  {
	    $all &= isset($_GET["c{$cat['id']}"]);
	    if (isset($_GET["c{$cat['id']}"]))
	    {
	      $wherecatina[] = $cat['id'];
	      $addparam .= "c{$cat['id']}=1&amp;";
	    }
	  }
	}

if ($all)
{
	$wherecatina = array();
  $addparam = "";
}

if (count($wherecatina) > 1)
	$wherecatin = implode(",",$wherecatina);
elseif (count($wherecatina) == 1)
	$wherea[] = "category = $wherecatina[0]";

if($license >0){
	$wherea[] = "license = $license";
}
if($user > 0){
	 $wherea[] = "owner = $user";
}
if($version > 0){
	$wherea[] = "version = $version";
}

$wherebase = $wherea;

if (isset($cleansearchstr))
{
	$wherea[] = "MATCH (search_text, ori_descr) AGAINST (" . sqlesc($searchstr) . ")";
	//$wherea[] = "0";
	$addparam .= "search=" . urlencode($searchstr) . "&amp;";
	$orderby = "";
	
	/////////////// SEARCH CLOUD MALARKY //////////////////////

    $searchcloud = sqlesc($cleansearchstr);
   // $r = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM searchcloud WHERE searchedfor = $searchcloud"), MYSQL_NUM);
    //$a = $r[0];
    //if ($a)
       // mysql_query("UPDATE searchcloud SET howmuch = howmuch + 1 WHERE searchedfor = $searchcloud");
    //else
       // mysql_query("INSERT INTO searchcloud (searchedfor, howmuch) VALUES ($searchcloud, 1)");
    mysql_query("INSERT INTO searchcloud (searchedfor, howmuch) VALUES ($searchcloud, 1)
                ON DUPLICATE KEY UPDATE howmuch=howmuch+1");
	/////////////// SEARCH CLOUD MALARKY END ///////////////////
}

$where = implode(" AND ", $wherea);
if (isset($wherecatin))
	$where .= ($where ? " AND " : "") . "category IN(" . $wherecatin . ")";

if ($where != "")
	$where = "WHERE $where";

$res = mysql_query("SELECT COUNT(*) FROM torrents $where") or die(mysql_error());
$row = mysql_fetch_array($res,MYSQL_NUM);
$count = $row[0];

if (!$count && isset($cleansearchstr)) {
	$wherea = $wherebase;
	$orderby = "ORDER BY id DESC";
	$searcha = explode(" ", $cleansearchstr);
	$sc = 0;
	foreach ($searcha as $searchss) {
		if (strlen($searchss) <= 1)
			continue;
		$sc++;
		if ($sc > 5)
			break;
		$ssa = array();
		foreach (array("search_text", "ori_descr") as $sss)
			$ssa[] = "$sss LIKE '%" . sqlwildcardesc($searchss) . "%'";
		$wherea[] = "(" . implode(" OR ", $ssa) . ")";
	}
	if ($sc) {
		$where = implode(" AND ", $wherea);
		if ($where != "")
			$where = "WHERE $where";
		$res = mysql_query("SELECT COUNT(*) FROM torrents $where");
		$row = mysql_fetch_array($res,MYSQL_NUM);
		$count = $row[0];
	}
}

$torrentsperpage = $CURUSER["torrentsperpage"];
if($page_limit)
	$torrentsperpage = $page_limit;
if (!$torrentsperpage)
	$torrentsperpage = 15;

if ($count)
//if(1)
{
	//list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, "browse.php?" . $addparam);
	$pager = pager($torrentsperpage, $count, "browse.php?" . $addparam);

	$query = "SELECT torrents.id, torrents.category, torrents.leechers, torrents.seeders, torrents.name, torrents.times_completed, torrents.size, torrents.added, torrents.type,  torrents.comments,torrents.numfiles,torrents.filename,torrents.owner,IF(torrents.nfo <> '', 1, 0) as nfoav," .
//	"IF(torrents.numratings < $minvotes, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, categories.name AS cat_name, categories.image AS cat_pic, users.username FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id $where $orderby $limit";
	"categories.name AS cat_name, categories.image AS cat_pic, users.username, torrents.version, torrents.descr,licenses.name AS lic_name,licenses.url AS lic_url,licenses.description AS lic_desc FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id LEFT JOIN licenses ON torrents.license = licenses.id $where $orderby {$pager['limit']}";
	$res = mysql_query($query) or die(mysql_error());
}
else
	unset($res);

if($count){
return array($res,$wherecatina, $pager);
}else{
	return array("",$wherecatina,"");
}
}
?>