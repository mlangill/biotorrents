<?php
require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn();
loggedinorreturn();
if (get_user_class() < UC_ADMINISTRATOR)
	stderr("Error", "Access denied.");
	
	
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if ($_POST["username"] == "" || $_POST["password"] == "" || $_POST["email"] == "")
		stderr("Error", "Missing form data.");
	if ($_POST["password"] != $_POST["password2"])
		stderr("Error", "Passwords mismatch.");
	if (!validemail($_POST['email']))
		stderr("Error", "Not valid email");
	
	$username = sqlesc($_POST["username"]);
	$password = $_POST["password"];
	$email = sqlesc($_POST["email"]);
	$secret = mksecret();
	$passhash = sqlesc(md5($secret . $password . $secret));
	$secret = sqlesc($secret);

	mysql_query("INSERT INTO users (added, last_access, secret, username, passhash, status, email) VALUES(NOW(), NOW(), $secret, $username, $passhash, 'confirmed', $email)") or sqlerr(__FILE__, __LINE__);
	$res = mysql_query("SELECT id FROM users WHERE username=$username");
	$arr = mysql_fetch_row($res);
	if (!$arr)
		stderr("Error", "Unable to create the account. The user name is possibly already taken.");
	header("Location: $BASEURL/userdetails.php?id=$arr[0]");
	die;
}
stdhead("Add user");
?>
<h1>Add user</h1>
<br />
<form method='post' action='adduser.php'>
<table border='1' cellspacing='0' cellpadding='5'>
<tr><td class='rowhead'>User name</td><td><input type='text' name='username' size='40' /></td></tr>
<tr><td class='rowhead'>Password</td><td><input type='password' name='password' size='40' /></td></tr>
<tr><td class='rowhead'>Re-type password</td><td><input type='password' name='password2' size='40' /></td></tr>
<tr><td class='rowhead'>E-mail</td><td><input type='text' name='email' size='40' /></td></tr>
<tr><td colspan='2' align='center'><input type='submit' value="Okay" class='btn' /></td></tr>
</table>
</form>
<?php stdfoot(); ?>