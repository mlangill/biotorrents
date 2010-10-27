<?php
ob_start("ob_gzhandler");
require_once "include/bittorrent.php";
require_once "include/html_functions.php";
require_once "include/user_functions.php";

dbconn();
loggedinorreturn();

if( (get_user_class() < UC_MODERATOR) || ($CURUSER['id'] !== '1')) //sysop id check
    stderr("Error", "Permission denied.");

$action = isset($_GET['action']) ? $_GET['action'] : ''; //if not goto default!


	switch($action) {
					case 'edit': 
					editForum();
					break;
					
					case 'takeedit':
					takeeditForum();
					break;
					
					case 'delete':
					deleteForum();
					break;
					
					case 'takedelete':
					takedeleteForum();
					break;
					
					case 'add':
					addForum();
					break;
					
					case 'takeadd':
					takeaddForum();
					break;
					
					default:
					showForums();
	
	}



function showForums() {
	stdhead("Forum Management Tools");
	echo '<span class="btn"><a href="forummanage.php?action=add">Add New</a></span><br /><br />';
	begin_main_frame();
	echo '<table width="700" border="0" align="center" cellpadding="2" cellspacing="0">';
	echo "<tr><td class='colhead' align='left'>Name</td><td class='colhead'>Topics</td><td class='colhead'>Posts</td><td class='colhead'>Read</td><td class='colhead'>Write</td><td class='colhead'>Create Topic</td><td class='colhead'>Modify</td></tr>";
	$result = mysql_query ("SELECT  * FROM forums ORDER BY sort ASC");
	if ( mysql_num_rows($result) > 0) {
	
		while($row = mysql_fetch_assoc($result)){
	
		echo "<tr><td><a href='forums.php?action=viewforum&amp;forumid=".$row["id"]."'><b>".htmlentities($row["name"], ENT_QUOTES)."</b></a><br />".htmlentities($row["description"], ENT_QUOTES)."</td>";
		echo "<td>".$row["topiccount"]."</td><td>".$row["postcount"]."</td><td>minimal " . get_user_class_name($row["minclassread"]) . "</td><td>minimal " . get_user_class_name($row["minclasswrite"]) . "</td><td>minimal " . get_user_class_name($row["minclasscreate"]) . "</td><td align='center' style=\"white-space: nowrap;\"><b><a href=\"?action=edit&amp;id=".$row["id"]."\">EDIT</a>&nbsp;|&nbsp;<a href=\"forummanage.php?action=delete&amp;id=".$row["id"]."\"><font color='red'>DELETE</font></a></b></td></tr>"; 
			  
	} 
	} 
	else 
	{print "<tr><td>Sorry, no records were found!</td></tr>";}       
	echo "</table>";
	
	end_main_frame();
	stdfoot();
}

function addForum() {
	global $CURUSER;

	stdhead("Add Forum");
	
	echo '<span class="btn"><a href="forummanage.php">Cancel</a></span><br /><br />';
	begin_main_frame();
?>

	<form method='post' action="forummanage.php?action=takeadd">
	<table width="600"  border="0" cellspacing="0" cellpadding="3" align="center">
		<tr align="center">
			<td colspan="2" class='colhead'>Make New Forum</td>
		</tr>
		<tr>
			<td><b>Forum Name</b></td>
			<td><input name="name" type="text" size="20" maxlength="60" /></td>
		</tr>
		<tr>
			<td><b>Forum Description</b></td>
			<td><input name="desc" type="text" size="30" maxlength="200" /></td>
		</tr>
		<tr>
			<td><b>Minimum Read Permission</b></td>
			<td><select name="readclass">
<?php
	$maxclass = get_user_class();
		for ($i = 0; $i <= $maxclass; ++$i)
		print("<option value='$i'" . ($CURUSER["class"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i) . "</option>\n");
?>
			</select>
			</td>
		</tr>
		<tr>
			<td><b>Minimum Write Permission</b></td>
			<td><select name='writeclass'>
<?php
	$maxclass = get_user_class();
		for ($i = 0; $i <= $maxclass; ++$i)
		print("<option value='$i'" . ($CURUSER["class"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i) . "</option>\n");
?>
			</select></td>
		</tr>
		<tr>
			<td><b>Minimun Create Topic Permission</b></td>
			<td><select name='createclass'>
<?php
	$maxclass = get_user_class();
		for ($i = 0; $i <= $maxclass; ++$i)
		print("<option value='$i'" . ($CURUSER["class"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i) . "</option>\n");
?>
			</select></td>
		</tr>
		<tr>
			<td><b>Forum Rank</b></td>
			<td><select name="sort">
<?php
	$res = mysql_query ("SELECT sort FROM forums");
	$nr = mysql_num_rows($res);
	$maxclass = $nr + 1;
		for ($i = 0; $i <= $maxclass; ++$i)
		print("<option value='$i'>$i </option>\n");
?>
			</select>
   
			</td>
		</tr>
 
		<tr align="center">
			<td colspan="2">
			<!--<input type="hidden" name="action" value="takeadd" /> -->
			<input type="submit" name="Submit" value="Make Forum" class="btn" /></td>
		</tr>
		</table>
          </form>
<?php
//	end_frame();
	end_main_frame();
	stdfoot();

}

function editForum() {

	stdhead("Edit Forum");
	
	$id = isset($_GET["id"]) ? (int)$_GET["id"] : stderr("Error", "Not Found");
	
	echo '<span class="btn"><a href="forummanage.php">Cancel</a></span><br /><br />';
	
	begin_frame("Edit Forum");
	$result = mysql_query ("SELECT * FROM forums where id = '$id'");
	if (mysql_num_rows($result) > 0) {
		while($row = mysql_fetch_assoc($result)){
?>

		<form method="post" action="forummanage.php?action=takeedit">
		<table width="600"  border="0" cellspacing="0" cellpadding="3" align="center">
		<tr align="center">
			<td colspan="2" class='colhead'>Edit Forum: <?php echo htmlentities($row["name"], ENT_QUOTES);?></td>
		</tr>
		<tr>
			<td><b>Forum Name</b></td>
			<td><input name="name" type="text" size="30" maxlength="60" value="<?php echo htmlentities($row["name"], ENT_QUOTES);?>" /></td>
		</tr>
		<tr>
			<td><b>Forum Description</b></td>
			<td><input name="desc" type="text" size="30" maxlength="200" value="<?php echo htmlentities($row["description"], ENT_QUOTES);?>" /></td>
		</tr>
		<tr>
			<td><b>Minimun Read Permission</b></td>
			<td><select name='readclass'>
<?php
	$maxclass = get_user_class();
		for ($i = 0; $i <= $maxclass; ++$i)
		if( get_user_class_name($i) != "" )
		print("<option value='$i'" . ($row["minclassread"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i) . "</option>");
?>
			</select>
			</td>
		</tr>
		<tr>
			<td><b>Minimal Post Rank</b></td>
			<td><select name='writeclass'>
<?php
	$maxclass = get_user_class();
		for ($i = 0; $i <= $maxclass; ++$i)
		if( get_user_class_name($i) != "" )
		print("<option value='$i'" . ($row["minclasswrite"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i)."</option>");
?>
			</select></td>
		</tr>
		<tr>
			<td><b>Minimal Create Topic Rank</b></td>
			<td><select name='createclass'>
<?php
	$maxclass = get_user_class();
		for ($i = 0; $i <= $maxclass; ++$i)
		if( get_user_class_name($i) != "" )
		print("<option value='$i'" . ($row["minclasscreate"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i)."</option>");
?>
			</select></td>
		</tr>
		<tr>
			<td><b>Forum Rank</b></td>
			<td><select name='sort'>
<?php
	$res = mysql_query ("SELECT sort FROM forums");
	$nr = mysql_num_rows($res);
	$maxclass = $nr + 1;
		for ($i = 0; $i <= $maxclass; ++$i)
		print("<option value='$i'" . ($row["sort"] == $i ? " selected='selected'" : "") . ">$i</option>");
?>
			</select>
			</td>
		</tr>
 
		<tr align="center">
			<td colspan="2">
			<input type="hidden" name="id" value="<?php echo $id;?>" />
			<input type="submit" name="Submit" value="Edit Forum" class="btn" />
			</td>
		</tr>
		</table>
 </form>
<?php
			}
	} 
	else 
	{print "Sorry, no records were found!";}      
	
//	end_frame();
	end_main_frame();
	stdfoot();
}

function takeaddForum() {
	if (!$_POST['name'] && !$_POST['desc']) { header("Location: forummanage.php"); die();}

	@mysql_query("INSERT INTO forums 
	(sort, name,  description,  minclassread,  minclasswrite, minclasscreate) VALUES(" . 
	sqlesc($_POST['sort']) . ", " . 
	sqlesc($_POST['name']). ", " . 
	sqlesc($_POST['desc']). ", " . 
	sqlesc($_POST['readclass']) . ", " . 
	sqlesc($_POST['writeclass']) . ", " . 
	sqlesc($_POST['createclass']) . ")");
	
	if(mysql_affected_rows() === 1)
		stderr("Success", "Forum Added. <a href='forummanage.php'>Return to Forum Management</a>");
	else
		stderr("Error", "There was an error. <a href='forummanage.php'>Return to Forum Management</a>");
die();

}

function takeeditForum() {

	if (!$_POST['name'] && !$_POST['desc'] && !$_POST['id']) { header("Location: forummanage.php"); die();}

	@mysql_query("UPDATE forums SET sort = " . 
	sqlesc($_POST['sort']) . ", name = " . 
	sqlesc($_POST['name']). ", description = " . 
	sqlesc($_POST['desc']). ", minclassread = " . 
	sqlesc($_POST['readclass']) . ", minclasswrite = " . 
	sqlesc($_POST['writeclass']) . ", minclasscreate = " . 
	sqlesc($_POST['createclass']) . " where id = ".
	sqlesc($_POST['id'])."");

	if(mysql_affected_rows() === 1)
		stderr("Success", "Forum Edited. <a href='forummanage.php'>Return to Forum Management</a>");
	else
		stderr("Error", "There was an error. <a href='forummanage.php'>Return to Forum Management</a>");
die();
}

function deleteForum() {

	$id = isset($_GET['id']) ? (int)$_GET['id'] : stderr("Error", "No fecking id!");
	
		
	$res = @mysql_query("SELECT id FROM topics WHERE forumid=$id");

    if (mysql_num_rows($res) >= 1) {
		stdhead();
		forum_select($id);
		stdfoot();
		exit();
	}
	else
		stderr("Warning", "You are about to delete a forum, there is no UNDELETE!! <a href='forummanage.php?action=takedelete&amp;id=$id'>Continue?</a>");
	
}


function takedeleteForum() {

$id = isset($_GET['id']) ? (int)$_GET['id'] : stderr("Error", "No fecking id!");
	
		if(!isset($_POST['deleteall'])) {
			$res = @mysql_query("SELECT id FROM topics WHERE forumid=$id");
			
			if (mysql_num_rows($res) == 0) 
				@mysql_query("DELETE FROM forums WHERE id=$id");
			
			(mysql_affected_rows() > 0) ? 
		stderr("Success", "Forum deleted return to <a href='forummanage.php'>Forum Management</a>") : stderr("Error", "Something bad happened!");
		}
		else
		{
			$forumid = (isset($_POST['forumid']) && ctype_digit($_POST['forumid'])) ? (int)$_POST['forumid'] : atserr("Error", "Nowhere to move to baby!");
			
			$res = @mysql_query("SELECT id FROM topics WHERE forumid=$id");
			
			if (mysql_num_rows($res) == 0) 
				stderr("Error", "There are no topics in this forum!");
			while($row = mysql_fetch_assoc($res)) 
				$tid[] = $row['id'];
			
			@mysql_query("UPDATE topics SET forumid=$forumid WHERE id IN (".join(',' , $tid).")");
			
			if(mysql_affected_rows() > 0)
			
				@mysql_query("DELETE FROM forums WHERE id=$id");
				
			(mysql_affected_rows() > 0) ? 
		stderr("Success", "Forum deleted return to <a href='forummanage.php'>Forum Management</a>") : stderr("Error", "Something bad happened!");
				
			
		}
	
}




function forum_select($currentforum = 0)
  {
    print("<p align='center'><form method='post' action='forummanage.php?action=takedelete&amp;id=$currentforum' name='jump'>\n");

    print("<input type='hidden' name='deleteall' value='true' />\n");

    print("Select Forum to move topics to: ");

    print("<select name='forumid'>\n");

    $res = mysql_query("SELECT * FROM forums ORDER BY name") or sqlerr(__FILE__, __LINE__);

    while ($arr = mysql_fetch_assoc($res))
    {
      if ($arr["id"] == $currentforum)
		continue;
        print("<option value='" . $arr["id"] . ($currentforum == $arr["id"] ? "' selected='selected'>" : "'>") . $arr["name"] . "</option>\n");
    }

    print("</select>\n");

    print("<input type='submit' value='Move To...' class='btn' />\n");

    print("</form>\n</p>");
  }

?>