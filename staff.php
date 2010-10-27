<?php
require_once "include/bittorrent.php";
require_once "include/html_functions.php";
require_once "include/user_functions.php";


dbconn();

loggedinorreturn();
stdhead("Staff");

$query = mysql_query("SELECT users.id, username, email, last_access, class, title, country, status, countries.flagpic, countries.name FROM users LEFT  JOIN countries ON countries.id = users.country WHERE class >=4 AND status='confirmed' ORDER BY username") or sqlerr();

while($arr2 = mysql_fetch_assoc($query)) {
	
/*	if($arr2["class"] == UC_VIP)
		$vips[] =  $arr2;
*/	
	if($arr2["class"] == UC_MODERATOR)
		$mods[] =  $arr2;
		
	if($arr2["class"] == UC_ADMINISTRATOR)
		$admins[] =  $arr2;
		
	if($arr2["class"] == UC_SYSOP)
		$sysops[] =  $arr2;
	}
/*
print_r($sysops);
print("<br />");
print_r($admins);
print("<br />");
print_r($mods);
print("<br />");
print(count($mods));
*/
function DoStaff($staff, $staffclass, $cols = 2) {
	global $pic_base_url;
	
	$dt = time() - 180;
	
	if($staff===false) {
		print("<br /><table width='75%' border='1' cellpadding='3'>");
		print("<tr><td class='colhead'><h2>{$staffclass}</h2></td></tr>");
		print("<tr><td>None defined yet!</td></tr></table>");
		return;
		}
	$counter = count($staff);
		
	$rows = ceil($counter/$cols);
	$cols = ($counter < $cols) ? $counter : $cols;
	//echo "<br />" . $cols . "   " . $rows;
	$r = 0;
	print("<br /><table width='75%' border='1' cellpadding='3'>");
	print("<tr><td class='colhead' colspan='{$counter}'><h2>{$staffclass}</h2></td></tr>");
	
	for($ia = 0; $ia < $rows; $ia++){

        echo "<tr>";
        for($i = 0; $i < $cols; $i++){
			if( isset($staff[$r]) )  {
			echo "<td><a href='userdetails.php?id={$staff[$r]['id']}'>".$staff[$r]["username"]."</a>".
			"   <img style='vertical-align: middle;' src='{$pic_base_url}staff".
			($staff[$r]['last_access']>$dt?"/online.gif":"/offline.gif" )."' border='0' alt='' />".
			"<a href='sendmessage.php?receiver={$staff[$r]['id']}'>".
			"   <img style='vertical-align: middle;' src='{$pic_base_url}staff/users.png' border='0' title=\"Personal Message\" alt='' /></a>".
			#"<a href='email-gateway.php?id={$staff[$r]['id']}'>".
			#"   <img style='vertical-align: middle;' src='{$pic_base_url}staff/mail.png' border='0' alt='{$staff[$r]['username']}' title=\"Send Mail\" /></a>".
			"   <img style='vertical-align: middle;' src='{$pic_base_url}flag/{$staff[$r]['flagpic']}' border='0' alt='{$staff[$r]['name']}' /></td>";
			$r++;
        }else{
			echo "<td>&nbsp;</td>";
			}
        }
        echo "</tr>";
		
        }
	print("</table>");
/*
print("</table>");
print("<br /><table border=1><tr>");
for ($i = 0; $i <= count($staff)-1; $i++) {
		print("<td>{$staff[$i]["username"]}</td>");
		}
		print("</tr></table>");
*/
}

print("<h1>Staff Page</h1>");

DoStaff($sysops, "Sysops");
isset($admins) ? DoStaff($admins, "Administrators") : DoStaff($admins=false, "Administrators");
isset($mods) ? DoStaff($mods, "Moderators") : DoStaff($mods=false, "Moderators");
//isset($vips) ? DoStaff($vips, "VIP's") : DoStaff($vips=false, "VIP's");


 if ($CURUSER['class'] >= UC_MODERATOR) { 
?>

<br />

<br />
		<table width="75%" cellpadding="10px">
		<tr><td class='colhead'>Staff Tools</td></tr>
		<!-- row 1 -->
		<tr><td>
		
			
			<span class="btn"><a href="bans.php">Bans</a></span>
			
			<span class="btn"><a href="adduser.php">Add New User</a></span>
			
			
			<span class="btn"><a href="recover.php">Recover Account</a></span>
			
			
			<span class="btn"><a href="users.php">Users List</a></span>
			
			<span class="btn"><a href="stats.php">Stats</a></span>
			
			</td></tr>
			<!-- row 2 -->
			<tr><td>
			
			<span class="btn"><a href="tags.php">BBCode Tags</a></span>
			

			<span class="btn"><a href="smilies.php">Smilies/Emoticons</a></span>
			
			<span class="btn"><a href="delacct.php">Delete Account</a></span>
			

			<span class="btn"><a href="stats.php">Tracker Statistics</a></span>
			
			</td></tr>
			<!-- roow 3 -->
			<tr><td>
			
			<span class="btn"><a href="testip.php">Test IP</a></span>
			

			<span class="btn"><a href="usersearch.php">User Search</a></span>
			

			<span class="btn"><a href="mysql_overview.php">MySQL Overview</a></span>
			

			<span class="btn"><a href="mysql_stats.php">MySQL Statistics</a></span>
			
			
			</td></tr>
			<!-- row 4 -->
			<tr><td>
			
			<span class="btn"><a href="forummanage.php">Manage Forum</a></span>
			

			<span class="btn"><a href="categories.php">Categories</a></span>
			
			<span class="btn"><a href="newusers.php">Newest Users</a></span>
			
			<span class="btn"><a href="resetpassword.php">Reset Password </a></span>
			
			</td></tr>
			<!-- row 5 -->
			<tr><td>
			
			<span class="btn"><a href="reputation_ad.php">Rep System</a></span>
			
			<span class="btn"><a href="reputation_settings.php">Rep Settings</a></span>
			
		</td></tr></table>
<?php }

stdfoot();

?>