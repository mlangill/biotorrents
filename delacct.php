<?php
require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn();
loggedinorreturn();

/*if( get_user_class() < UC_MODERATOR )
	stderr('Error', 'Move along, nothing to see here'); */
	
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
  $username = trim($_POST["username"]);
  $password = trim($_POST["password"]);
  if (!$username || !$password)
    stderr("Error", "Please fill out the form correctly.");
  $res = mysql_query(

  "SELECT * FROM users WHERE username=" . sqlesc($username) .
  " && passhash=md5(concat(secret,concat(" . sqlesc($password) . ",secret)))") or sqlerr();
  if (mysql_num_rows($res) != 1)
    stderr("Error", "Bad user name or password. Please verify that all entered information is correct.");
  $arr = mysql_fetch_assoc($res);

  $id = $arr['id'];
  $res = mysql_query("DELETE FROM users WHERE id=$id") or sqlerr();
  if (mysql_affected_rows() != 1)
    stderr("Error", "Unable to delete the account.");
  stderr("Success", "The account was deleted.");
}
stdhead("Delete account");
?>
<h1>Delete account</h1>
<form method='post' action='delacct.php'>
<table border='1' cellspacing='0' cellpadding='5'>
<tr><td class='rowhead'>User name</td><td><input size='40' name='username' /></td></tr>
<tr><td class='rowhead'>Password</td><td><input type='password' size='40' name='password' /></td></tr>
<tr><td colspan='2'><input type='submit' class='btn' value='Delete' /></td></tr>
</table>
</form>
<?php
stdfoot();
?>